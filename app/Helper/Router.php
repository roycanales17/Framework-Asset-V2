<?php

    namespace app\Helper;

    use app\Handler\RouteHandler;
    use ReflectionClass;

    class Router
    {
        use Mapping;

        private RouteHandler $route;
        private array $params = [];

        function __construct(RouteHandler $handler) {
            $this->route = $handler;
        }

        public
        function search(string $property): false|string
        {
            $obj = $this->route;
            $reflection = new ReflectionClass($obj);
            $routes = $reflection->getProperty($property);
            $routes->setAccessible(true);
            $lists = $routes->getValue($obj);
            $url = trim($this->getURI(), '/');

            foreach ($lists as $uri => $action) {

                $matched = 0;
                $uri = $this->URISlashes($uri);
                $route_uri = $this->separateSubDirectories($uri);
                $route_url = $this->separateSubDirectories($url);

                if (count($route_uri) === count($route_url)) {
                    foreach ($route_uri as $index => $directory) {
                        if (isset($route_url[$index])) {
                            if (preg_match('/^\{[^{}]+\}$/', $directory)) {
                                $this->params[str_replace(['{', '}'], '', $directory)] = preg_replace('/\?.*/', '', $route_url[$index]);
                                $matched++;
                            } else {
                                if (strtolower($directory) === strtolower(strstr($route_url[$index], '?', true) ?: $route_url[$index])) {
                                    $matched++;
                                }
                            }
                        }
                    }
                } else {
                    $matched = -1;
                }

                if ($matched === count($route_uri)) {
                    request()->setParams($this->params);
                    return $action;
                }
            }

            return false;
        }

        private
        function URISlashes(?string $uri, array $prefixes = []): string
        {
            if ($uri === null || $uri === '') {
                return '';
            }

            if ($uri[0] !== '/') {
                $uri = '/' . $uri;
            }

            if ($uri[-1] !== '/') {
                $uri .= '/';
            }

            $uri = trim($uri, '/');
            return implode('/', $prefixes) . '/' . $uri;
        }

        private
        function separateSubDirectories(?string $value): array
        {
            return array_values(array_filter(explode('/', $value), function ($value) {
                return $value !== "";
            }));
        }
    }