<?php

    namespace config\Middleware;

    use App\Config;
    use App\Http\Requests\Request;
    use App\Scheme\Middleware;

    class Cores extends Middleware
    {
        public function handle(Request $request)
        {
            return true;
        }
    }