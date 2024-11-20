<?php

    namespace app\Helper;

    use app\Handler\RouteHandler;
    use ReflectionClass;

    class Router
    {
        use Mapping;

        private RouteHandler $route;

        function __construct(RouteHandler $handler) {
            $this->route = $handler;
        }

        public
        function search(): false|string
        {
            $obj = $this->route;
            $reflection = new ReflectionClass($obj);

            $routes = $reflection->getProperty('routes');
            $routes->setAccessible(true);

            foreach ($routes->getValue($obj) as $uri => $path) {
                if (trim($uri, '/') == trim($this->getURI(), '/')) {
                    return $path;
                }
            }

            return false;
        }
    }