<?php
	
	use Core\Application;
	use Handler\ExceptionHandler;
	use Handler\MiddlewaresHandler;
	use Handler\PageInitializer;
	use Handler\RouteHandler;
	use app\Exceptions\CustomException;
	use app\Middleware\Cores;
	
	# Start the application
    return Application::configure('env')
        ->withPageInit(function (PageInitializer $init) {
            $init->siteHeader('/', app\Initializer\Web::class);
        })
        ->withMiddlewares(function (MiddlewaresHandler $middleware) {
            $middleware->addWebMiddleware(Cores::class);
        })
        ->withRoutes(function (RouteHandler $route) {
            $route->page('error', 'error.php');
        })
        ->withExceptions(function (ExceptionHandler $handler) {
            $handler->handle(CustomException::class);
        })
        ->throw('notFound')
        ->main('home')
        ->render();