<?php

    namespace app\Helper;

    use App\Config;
    use Exception;

    trait Mapping
    {
        protected
        function getURI(bool $fullPath = false): string
        {
            $requestUri = $_SERVER['REQUEST_URI'];
            $basePath = config('base_path');
            $requestURI = substr($requestUri, strlen($basePath));
            $parsedUrl = parse_url(filter_var($requestURI, FILTER_SANITIZE_URL));
            $path = isset($parsedUrl['path']) ? filter_var($parsedUrl['path'], FILTER_SANITIZE_SPECIAL_CHARS) : '';

            if ($fullPath)
                return root ."/pages".rtrim($path, '/');

            return $path;
        }

        protected
        function createFullPath(string $path): string
        {
            $path = trim($path, '/');
            if (substr($path, -4) !== '.php') {
                $path .= '.php';
            }
            return root . '/pages/' . $path;
        }


        protected
        function isInDirectory(string $path, string $dir): bool
        {
            $normalizedPath = '/' . ltrim($path, '/');
            $normalizedDir = rtrim($dir, '/') . '/';

            return str_starts_with($normalizedPath, $normalizedDir);
        }

        protected
        function isFileExist(string $path): bool
        {
            if (!file_exists($path = $this->createFullPath($path))) {
                throw new Exception("`$path` does not exist");
            }

            return true;
        }

        protected
        function isDirectoryExist(string $path): bool
        {
            if (!is_dir($path = $this->createFullPath($path))) {
                throw new Exception("`$path` does not exist");
            }

            return true;
        }
    }