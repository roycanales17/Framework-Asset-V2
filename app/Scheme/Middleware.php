<?php

    namespace app\Scheme;

    use App\Http\Requests\Request;

    abstract class Middleware
    {
        public abstract function handle(Request $request);
    }