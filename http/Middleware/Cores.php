<?php

    namespace http\Middleware;

    use App\Request;
    use App\Scheme\Middleware;

    class Cores extends Middleware
    {
        public function handle(Request $request): bool
        {
            return true;
        }
    }