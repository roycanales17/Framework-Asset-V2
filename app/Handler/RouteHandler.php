<?php

    namespace app\Handler;

    class RouteHandler
    {
        private array $routes = [];

        public
        function render(string $uri, string $path): self
        {
            $this->routes[trim($uri, '/')] = '/' . ltrim($path, '/');
            return $this;
        }
    }