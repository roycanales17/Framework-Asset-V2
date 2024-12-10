<?php

    namespace app\Scheme;

    use App\Request;

    abstract class Components implements Modules
    {
        private string $id;
        private string $token;
        private string $name;
        private string $parser;
        private float $startedTime;
        private array $actions = [];
        private static string $key = 'app-component';
        protected array $events = [];

        public
        function __construct() {
            $length = strlen(get_called_class());
            $this->name = strtolower(get_called_class());
            $this->id = bin2hex(random_bytes(intval($length / 2)));

            if (!($_SESSION[self::$key] ?? false)) {
                $_SESSION[self::$key] = [];
            }

            if (!($_SESSION[self::$key][$this->name] ?? false)) {
                $_SESSION[self::$key][$this->name] = bin2hex(random_bytes(intval($length * 2) / 2));
            }

            $this->startedTime = microtime(true);
            $this->token = $_SESSION[self::$key][$this->name];
            $this->parser = config('HTML_PARSER', 'dom');

            foreach ($this->events as $event) {
                $this->actions[$event] = $this->moduleEncryptedAction($event);
            }
        }

        protected
        function inputToken(): string
        {
            return trim(<<<HTML
                <input type="hidden" name="__token__" value="{$this->token}" />
            HTML);
        }

        protected
        function formAction(string $success, string $fail = '', string $loader = ''): string
        {
            $success = $this->moduleEncryptedAction($success);

            if ($fail)
                $fail = $this->moduleEncryptedAction($fail);

            if ($loader)
                $loader = $this->moduleEncryptedAction($loader);

            return trim(<<<HTML
                 onsubmit="return $$.form('$success','$fail', '$loader',event)" method='post'
            HTML);
        }

        protected
        function identifier(): string
        {
            return "data-module='{$this->token}' id='{$this->id}'";
        }

        private
        function replaceWithJSListener(string $rendered): string
        {
            preg_match_all('/(?:\$\$|this)\.listen\s*\(\s*["\']([^"\']+)["\']/', $rendered, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $originalFunc = $match[1];
                $encryptedFunc = $this->moduleEncryptedAction($originalFunc);
                $rendered = preg_replace_callback(
                    '/(?:\$\$|this)\.listen\s*\(\s*["\']' . preg_quote($originalFunc, '/') . '["\']/',
                    function ($matches) use ($encryptedFunc, $originalFunc) {
                        return str_replace($originalFunc, $encryptedFunc, $matches[0]);
                    },
                    $rendered
                );
            }
            return $rendered;
        }

        private
        function replaceWithJSModule(string $rendered): string
        {
            $originalRendered = $rendered;
            $patternArrow = '/(\$\$.module\(\s*)\(\s*(.*?)\s*\)\s*(=>\s*\{)/';
            $replacementArrow = "$1'{$this->id}',($2)$3";
            if (!preg_match("/\$\$.module\(\s*'{$this->id}',/", $rendered)) {
                $rendered = preg_replace($patternArrow, $replacementArrow, $rendered);
            }

            if ($rendered === $originalRendered) {
                $patternFunction = '/(\$\$.module\(\s*)\s*(function\s*\([^)]*\)\s*\{)/';
                $replacementFunction = "$1'{$this->id}', $2";
                if (!preg_match("/\$\$.module\(\s*'{$this->id}',/", $rendered)) {
                    $rendered = preg_replace($patternFunction, $replacementFunction, $rendered);
                }
            }

            return $rendered;
        }

        private
        function replaceWithContainer(string $rendered): string
        {
            $container = "<div {$this->identifier()} style='all: unset;display: contents;'>";
            $rendered = preg_replace('/<>/', $container, $rendered, 1);
            $rendered = preg_replace('/<>/', '', $rendered);
            $rendered = preg_replace_callback('/<\/>/', function() {
                static $count = 0;
                $count++;
                if ($count === 1) {
                    return '</div>';
                }
                return '';
            }, $rendered);
            return str_replace('</>', '', $rendered);
        }

        private
        function replaceWithAjaxRequest(string $rendered): string
        {
            $pattern = '/((?:this|\$\$)\.ajax)\(\s*(\{.*?}|\[.*?]|["\'].*?["\']|[^)]+?)\s*\)/s';
            $replacement = '$1($2, \'' . $this->token . '\')';

            if (!preg_match('/((?:this|\$\$)\.ajax)\(\s*([^,]+),/', $rendered)) {
                $rendered = preg_replace($pattern, $replacement, $rendered);
            }
            return $rendered;
        }

        private
        function replaceWithComponents(string $rendered): string
        {
            preg_match_all('/<([A-Z][\w\.-]*)\b[^>]*>/', $rendered, $matches);
            $customTags = array_diff($matches[1], self::standardTags);

            $customTagAttributes = [];
            foreach (array_unique($customTags) as $customTag) {
                preg_match_all(
                    sprintf(
                        '/<%s\b[^>]*\/>|<%s\b[^>]*>(.*?)<\/%s>/is',
                        preg_quote($customTag, '/'),
                        preg_quote($customTag, '/'),
                        preg_quote($customTag, '/')
                    ),
                    $rendered,
                    $tagMatches,
                    PREG_OFFSET_CAPTURE
                );

                foreach ($tagMatches[0] as $match) {
                    $fullTag = $match[0];
                    $offset = $match[1];

                    preg_match_all(
                        '/\s([a-zA-Z][a-zA-Z0-9-]*)=(".*?"|\'[^\']*\'|[^"\'>\s]*)/',
                        $fullTag,
                        $attributeMatches,
                        PREG_SET_ORDER
                    );

                    $attributes = [];
                    foreach ($attributeMatches as $attrMatch) {
                        $attributeName = $attrMatch[1];
                        $attributeValue = trim($attrMatch[2], '\'"');
                        $attributes[$attributeName] = $attributeValue;
                    }

                    $content = '';
                    if (!str_ends_with(trim($fullTag), '/>')) {
                        $closeTagPattern = sprintf(
                            '/<%s\b[^>]*>(.*?)<\/%s>/is',
                            preg_quote($customTag, '/'),
                            preg_quote($customTag, '/')
                        );
                        if (preg_match($closeTagPattern, $rendered, $contentMatch, 0, $offset)) {
                            $content = $contentMatch[1];
                        }
                    }

                    $customTagAttributes[] = [
                        'tag' => $customTag,
                        'attributes' => $attributes,
                        'content' => $content,
                        'full_tag' => trim($fullTag)
                    ];
                }
            }

            $customTagAttributes = array_reverse($customTagAttributes);
            foreach ($customTagAttributes as $customTag) {
                $tag = str_replace('.', '\\', $customTag['tag']);
                $params = $customTag['attributes'];
                $content = $customTag['content'];
                $fullTag = $customTag['full_tag'];

                if (class_exists($tag) || class_exists($tag = 'includes\\' . $tag)) {
                    if ($content) {
                        $params['children'] = $content;
                    }
                    $replacement = render($tag, $params);
                } else {
                    $replacement = '';
                }

                return $this->replaceWithComponents(str_replace($fullTag, $replacement, $rendered));
            }

            return $rendered;
        }

        private
        function moduleEncryptedAction($string): string
        {
            if (!preg_match('/^[a-f0-9]{20}$/i', $string)) {
                $combined = $string . $this->id . $this->token;
                $hash = hash('sha256', $combined);
                return substr($hash, 0, 20);
            }
            return $string;
        }

        public
        function build(string $rendered): string
        {
            $rendered = $this->replaceWithContainer($rendered);
            $rendered = $this->replaceWithJSModule($rendered);
            $rendered = $this->replaceWithJSListener($rendered);
            $rendered = $this->replaceWithAjaxRequest($rendered);
            $rendered = $this->replaceWithComponents($rendered);

            $timeDuration = microtime(true) - $this->startedTime;
            $timeMilliseconds = $timeDuration * 1000;
            return "<!-- Time Duration: " . sprintf('%.2f', $timeMilliseconds) . " ms -->". $rendered;
        }

        public
        function ajax(Request $request): mixed
        {
            return $request->response(400)->json([
                'message' => 'Bad Request',
                'payload' => $request->except(['__token__'])
            ]);
        }

        public
        function getEvents(): array
        {
            return $this->actions;
        }
    }