<?php

    # Global constant
    define('root', dirname(__DIR__));

    # Class Importer
    spl_autoload_register(fn($class) => file_exists($path = root . '/' . str_replace('\\', '/', $class) . '.php') && require_once $path);

    # Built-In Functions
    foreach (['/app/Standards.php','/vendor/autoload.php'] as $file) {
        if (file_exists(root . $file)) {
            require_once root . $file;
        }
    }

    # Startup Application
    if (defined('ARTISAN')) {

        # Session
        session_start();

        die(require root. '/app/Terminal.php');
    } else {

        try {
            Core\Session::setup();
            die(require root. '/app/Kernel.php');

        } catch (Error|Exception|Throwable|ParseError $e) {
            die(response(500)->html((new Helper\Collect)->displayError($e)));
        }
    }