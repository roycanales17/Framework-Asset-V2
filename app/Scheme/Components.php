<?php

    namespace app\Scheme;

    use App\Http\Requests\Request;

    abstract class Components
    {
        protected string $id;
        private string $token;
        private string $name;
        private static string $key = 'app-component';

        public
        function __construct() {
            $this->name = strtolower(get_called_class());

            if (!($_SESSION[self::$key] ?? false)) {
                $_SESSION[self::$key] = [];
            }

            if (!($_SESSION[self::$key][$this->name] ?? false)) {
                $_SESSION[self::$key][$this->name] = bin2hex(random_bytes(32 / 2));
            }

            $this->token = $_SESSION[self::$key][$this->name];
        }

        public
        function build(array $params = []): string
        {
            $name = "id='".($this->id ?? '')."'";
            $module = "data-module='{$this->token}'";
            $container = "<div $module $name>";

            $rendered = $this->render($params);
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

        protected
        function token(): string
        {
            return <<<HTML
                <input type="hidden" name="token" value="{$this->token}" />
            HTML;
        }

        public
        abstract function render(array $params = []): string;

        public
        abstract function ajax(Request $request): mixed;
    }