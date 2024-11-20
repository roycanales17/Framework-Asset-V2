<?php

    # Session
    session_start();
    define('root', dirname(__DIR__));

    # Class Importer
    spl_autoload_register(function ($class) {
        $path = root . '/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    });

    require root. '/vendor/autoload.php';
    require root. '/app/Standard.php';
    require root. '/config/Kernel.php';