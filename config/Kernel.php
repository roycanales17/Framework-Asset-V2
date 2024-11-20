<?php

    use app\Application;
    use app\Handler\ExceptionHandler;
    use app\Handler\MiddlewaresHandler;
    use app\Handler\PageInitializer;
    use app\Handler\RouteHandler;
    use config\Exceptions\CustomException;
    use config\Middleware\Cores;

    # Start the application
    Application::configure('env')
        ->withPageInit(function (PageInitializer $init) {
            $init->siteHeader('/', \config\Initializer\Web::class);
        })
        ->withExceptions(function (ExceptionHandler $handler) {
            $handler->handle(CustomException::class);
        })
        ->withMiddlewares(function (MiddlewaresHandler $middleware) {
            $middleware->addWebMiddleware(Cores::class);
        })
        ->withRoutes(function (RouteHandler $route) {
            $route->render('error', 'error.php');
        })
        ->throw('notFound')
        ->main('home')
        ->render();