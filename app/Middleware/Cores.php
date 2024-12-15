<?php

    namespace app\Middleware;

    use Core\Request;
	use Scheme\Middleware;
	
	class Cores extends Middleware
    {
        public function handle(Request $request): bool
        {
            return true;
        }
    }