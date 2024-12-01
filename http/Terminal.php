<?php

    $cmd = new App\Command();
    $cmd->register( "make", "exception", "Generate a new exception class for custom error handling." );
    $cmd->register( "make", "components", "Generate new components class." );
    $cmd->register( "make", "middleware", "Generate new middleware class for handling HTTP request pipelines." );
    $cmd->register( "make", "model", "Generate new model class." );
    $cmd->register( "clear", "logs", "Remove all files from logs directory." );
    $cmd->run($argv);