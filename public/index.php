<?php

    # Global constant
    define('root', dirname(__DIR__));

    # Class Importer
    spl_autoload_register(fn($class) => file_exists($path = root . '/' . str_replace('\\', '/', $class) . '.php') && require_once $path);

    # Built-In Functions
    foreach (['/app/Standard.php', '/vendor/autoload.php'] as $file) {
        if (file_exists(root . $file)) {
            require_once root . $file;
        }
    }

    # Session
    app\Session::start();

    # Startup Application
    if (defined('ARTISAN')) {
        die(require root. '/http/Terminal.php');
    } else {
        die(require root. '/http/Kernel.php');
    }