<?php

    namespace app\Handler;

    class RouteHandler
    {
        private array $routes = [];
        private array $render = [];

        public
        function page(string $uri, string $path): void
        {
            if (file_exists(root . 'pages/' . $path)) {
                $this->routes[trim($uri, '/')] = '/' . ltrim($path, '/');
            }
        }

        public
        function render(string $uri, string $classNAme): void
        {
            if (class_exists($classNAme)) {
                $this->render[trim($uri, '/')] = $classNAme;
            }
        }

        //        public
        //        function where(string|array $pattern): self
        //        {
        //            // Todo: we can work on this in the future...
        //            return $this;
        //        }
    }