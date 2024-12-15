<?php

    namespace http\Initializer;
    use Scheme\Initializer;

    class Web implements Initializer
    {
        public function onLoad(): void
        {
            date_default_timezone_set('Asia/Manila');
            ini_set('display_errors', config('development'));
            ini_set('error_log', '/logger/errors.log');
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
        }

        public function onExit(): void
        {
            // You can leave here empty...
        }
    }