<?php

    namespace app;

    use app\Handler\ExceptionHandler;
    use app\Handler\MiddlewaresHandler;
    use app\Handler\PageInitializer;
    use App\Handler\RouteHandler;
    use app\Helper\Initializer;
    use app\Helper\Mapping;
    use app\Helper\Reflections;
    use app\Helper\Router;
    use app\Helper\Skeleton;
    use App\Http\Requests\Request;
    use Closure;
    use Error;
    use Exception;
    use ParseError;
    use ReflectionClass;
    use Throwable;

    class Application
    {
        use Mapping;
        use Reflections;
        use Skeleton;

        private string $dir;
        private string $config;
        private string $main = '';
        private string $notFound = '';
        private null|object $middleware = null;
        private null|object $exceptions = null;
        private null|RouteHandler $routes = null;
        private null|PageInitializer $init = null;

        public
        static function configure(string $init = ''): self
        {
            # Set a default constant
            Config::set('development', Config::get('environment') == 'local');

            # Create new instance
            return new self($init);
        }

        public
        function __construct(string $config)
        {
            $backtrace = debug_backtrace();
            $this->dir = dirname($backtrace[1][ 'file' ]);
            $this->config = trim($config, '/');
            return $this;
        }

        public
        function withExceptions(Closure $callback): self
        {
            $callback($this->exceptions = new ExceptionHandler());
            return $this;
        }

        public
        function withMiddlewares(Closure $callback): self
        {
            $callback($this->middleware = new MiddlewaresHandler());
            return $this;
        }

        public
        function withRoutes(Closure $callback): self
        {
            $callback($this->routes = new RouteHandler());
            return $this;
        }

        public
        function withPageInit(Closure $callback): self
        {
            $callback($this->init = new PageInitializer());
            return $this;
        }

        public
        function throw(string $page = ''): self
        {
            $this->notFound = ltrim($page, '/') . '.php';
            return $this;
        }

        public
        function main(string $page): self
        {
            $this->main = ltrim($page, '/') . '.php';
            return $this;
        }

        private
        function validate(string $filepath)
        {
            if ($this->middleware) {
                $reflection = new ReflectionClass($this->middleware);
                $method = $reflection->getMethod('fetchMiddlewares');
                $method->setAccessible(true);

                foreach ($method->invoke($this->middleware) as $action) {
                    $path = $action['path'];
                    $action = $action['action'];

                    $class = null;
                    $method = null;
                    $args = null;

                    if (is_string($action)) {
                        $class = $action;
                        $method = 'handle';
                    } else {
                        $class = $action[0];
                        $method = $action[1];
                        $args = $action[2] ?? null;
                    }

                    if ($path == '*') {
                        $res = $this->performAction([$class, $method, $args]);
                    } else {
                        if ($filepath == $this->createFullPath($path)) {
                            $res = $this->performAction([$class, $method, $args]);
                        }
                    }

                    if (($res ?? true) === false) {
                        return (new Request)->response(401)->json("Unauthorized");
                    }
                    if (http_response_code() !== 200) {
                        return $res;
                    }
                }
            }
            return true;
        }

        private
        function commence(string $path): string
        {
            $onStart = false;
            $onExit = false;
            if (!is_null($this->init)) {
                $initReflection = new ReflectionClass($this->init);
                $configs = $initReflection->getProperty('configs');
                $configs->setAccessible(true);

                foreach ($configs->getValue($this->init) as $config) {
                    $is_dir = is_dir($config['path']);
                    $passed = false;

                    if ($is_dir) {
                        $dir = str_replace(root .'/pages', '', $config['path']);
                        $semiPath = str_replace(root .'/pages', '', $path);

                        if ($this->isInDirectory($semiPath, $dir)) {
                            $passed = true;
                        }
                    } else {
                        if ($config['path'] == $path) {
                            $passed = true;
                        }
                    }

                    if ($passed) {
                        call_user_func($config['callback'], $setup = new Initializer());
                        $setupReflection = new ReflectionClass($setup);
                        $method = $setupReflection->getMethod('getSetup');
                        $method->setAccessible(true);
                        $conf = $method->invoke($setup);

                        if ($classHeader = $conf['header']) {
                            $onStart = new $classHeader();
                        }

                        if ($classHeader = $conf['footer']) {
                            $onExit = new $classHeader();
                        }
                    }
                }
            }

            ob_start();
            if ($onStart) {
                echo(call_user_func([new $onStart, 'onLoad']));
            }

            if ( ($res = $this->validate($path)) === true ) {
                require_once $path;
            } else {
                echo($res);
            }

            if ($onExit) {
                echo(call_user_func([new $onExit, 'onExit']));
            }

            return ob_get_clean();
        }

        public
        function render(): void
        {
            try {

                // Search in routes
                if (!is_null($this->routes)) {
                    $route = new Router($this->routes);

                    if ($routePath = $route->search()) {
                        die($this->commence($this->createFullPath($routePath)));
                    }
                }

                $uri = $this->getURI();
                if ($uri == '/') {
                    $uri = $this->main;
                }

                // Search in files
                if (file_exists($path = $this->createFullPath($uri))) {
                    die($this->commence($path));
                } else {
                    $throw = $this->createFullPath($this->notFound);
                    if (file_exists($throw)) {
                        die(require $throw);
                    } else {
                        die((new Request)->response(404)->json(['message' => "Page not found!"]));
                    }
                }

            } catch (Error|Exception|Throwable|ParseError $e) {
                if (is_null($this->exceptions)) {
                    die($this->displayError($e));
                }

                die($this->exceptions->handleException($e, (new Request)));
            }
        }
    }