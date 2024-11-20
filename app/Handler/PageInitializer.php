<?php

    namespace app\Handler;

    use app\Helper\Initializer;
    use Closure;

    class PageInitializer
    {
        private array $configs = [];

        public
        function file(string $filePath, Closure $callback): void
        {
            $this->register($this->build(rtrim($filePath, '.php')) . '.php', $callback);
        }

        public
        function directory(string $directory, Closure $callback): void
        {
            $this->register(rtrim($this->build($directory), '/') .'/', $callback);
        }

        public
        function siteHeader(string $filepathOrDirectory, string $className): void
        {
            $this->register($this->build($filepathOrDirectory), function(Initializer $init) use ($className) {
                $init->onLoad($className);
            });
        }

        public
        function siteFooter(string $filepathOrDirectory, string $className): void
        {
            $this->register($this->build($filepathOrDirectory), function(Initializer $init) use ($className) {
                $init->onExit($className);
            });
        }

        private
        function register(string $path, $callback): void
        {
            $this->configs[] = [
                'path' => $path,
                'callback' => $callback
            ];
        }

        private
        function build(string $path): string
        {
            return root . '/pages/' . trim( $path, '/' );
        }
    }