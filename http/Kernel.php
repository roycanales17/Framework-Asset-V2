<?php

    use app\Application;
    use app\Handler\ExceptionHandler;
    use app\Handler\MiddlewaresHandler;
    use app\Handler\PageInitializer;
    use app\Handler\RouteHandler;
    use http\Exceptions\CustomException;
    use http\Middleware\Cores;

    # Start the application
    return Application::configure('env')
        ->withPageInit(function (PageInitializer $init) {
            $init->siteHeader('/', \http\Initializer\Web::class);
        })
        ->withMiddlewares(function (MiddlewaresHandler $middleware) {
            $middleware->addWebMiddleware(Cores::class);
        })
        ->withRoutes(function (RouteHandler $route) {
            $route->render('error', 'error.php');
        })
        ->withExceptions(function (ExceptionHandler $handler) {
            $handler->handle(CustomException::class);
        })
        ->throw('notFound')
        ->main('home')
        ->render();