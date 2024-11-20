<?php

    namespace app\Handler;

    use App\Helper\Reflections;
    use app\Helper\Skeleton;
    use App\Http\Requests\Request;
    use App\Logger;
    use Closure;

    class ExceptionHandler
    {
        use Reflections;
        use Skeleton;

        protected array $handlers = [];

        public function handle(string $exceptionClass, Closure $handler = null): void
        {
            $this->handlers[$exceptionClass] = $handler;
        }

        public function handleException($exception, Request $request = null): string
        {
            Logger::path('errors.log')->exception($exception);

            foreach ($this->handlers as $exceptionClass => $handler) {
                if ($exception instanceof $exceptionClass) {

                    if (is_object($handler)) {
                        return $handler($exception, $request);
                    }

                    if (method_exists($exception, 'render')) {
                        return $exception->render($request);
                    }

                    return $exception->displayError();
                }
            }

            return $this->displayError($exception);
        }
    }
