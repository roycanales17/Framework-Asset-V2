<?php

    namespace app\Handler;

    use app\Helper\Mapping;
    use Exception;

    class MiddlewaresHandler
    {
        use Mapping;

        private
        array $registered = [];

        public
        function addWebMiddleware(string $class): void
        {
            if ($this->validateAction($class)) {
                $this->registered[] = [
                    'path' => '*',
                    'action' => $class
                ];
            }
        }

        public
        function addPageMiddleware(string $path, string|array $action): void
        {
            if ($this->isFileExist($path)) {
                $this->registerMiddleware($path, $action);
            }
        }

        public
        function addDirectoryMiddleware(string $path, string|array $action): void
        {
            if ($this->isDirectoryExist($path)) {
                $this->registerMiddleware($path, $action);
            }
        }

        private
        function registerMiddleware(string $path, string|array $action): void
        {
            if (is_string($action)) {
                if ($this->validateAction($action)) {
                    $this->registered[] = [
                        'path' => $path,
                        'action' => $action
                    ];
                }
            } else {
                if (is_array($action)) {
                    if (!in_array(count($action), [2, 3])) {
                        throw new Exception("Action array must contain at least 2 or 3.");
                    }

                    $class = $action[0];
                    $method = $action[1];
                    if ($this->validateAction($class, $method)) {
                        $this->registered[] = [
                            'path' => $path,
                            'action' => $action
                        ];
                    }
                }
            }
        }

        private
        function validateAction(string $className, string $method = 'handle'): bool
        {
            if (class_exists($className)) {
                if (!$method) {
                    throw new Exception("Class {$className}: method is required.");
                }
                if (!method_exists($className, $method)) {
                    throw new Exception("Class {$className}: method `$method` is not exist.");
                }
            } else {
                throw new Exception("Class `{$className}` does not exist.");
            }

            return true;
        }

        private
        function fetchMiddlewares(): array
        {
            return $this->registered;
        }
    }