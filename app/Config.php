<?php

    namespace App;

    class Config
    {
        private
        static array $environment = [];

        public
        static function get(string $keyword, string $default = ''): string
        {
            if (self::$environment) {
                return self::$environment[$keyword] ?? $default;
            }

            $env = [];
            $lines = file(root.'/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '#') === 0) {
                    continue;
                }

                if (strpos($line, '=') === false) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $value = trim($value, '"\'');
                $env[trim($key)] = $value;
            }

            self::$environment = $env;
            return $env[$keyword] ?? $default;
        }

        public
        static function set(string $key, mixed $value): void
        {
            self::$environment[$key] = $value;
        }
    }