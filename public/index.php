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

    require root. '/app/Standard.php';

    $autoload = root. '/vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }

    if (defined('ARTISAN')) {
        $cmd = new App\Command();
        $cmd->register( "make", "components", "Generate new components class." );
        $cmd->register( "make", "model", "Generate new model class." );
        $cmd->register( "clear", "logs", "Remove all files from logs directory." );
        $cmd->run( $argv );
    } else {
        die(require root. '/http/Kernel.php');
    }