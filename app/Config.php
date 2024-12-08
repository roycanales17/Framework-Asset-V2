<?php

    namespace App;

    class Config
    {
        private
        static array $environment = [];

        public static function get(string $keyword, string $default = ''): mixed
        {
            $keyword = strtoupper($keyword);
            if (isset(self::$environment[$keyword])) {
                return (string) self::$environment[$keyword];
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

                if (is_numeric($value)) {
                    $value = (int) $value;
                }

                if ($value === 'true') {
                    $value = true;
                }

                if ($value === 'false') {
                    $value = false;
                }

                $env[trim($key)] = $value;
            }

            self::$environment = $env;
            return $env[$keyword] ?? $default;
        }

        public
        static function define(string $key, mixed $value): void
        {
            self::$environment[$key] = $value;
        }
    }