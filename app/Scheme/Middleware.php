<?php

    namespace app\Scheme;

    use App\Request;

    abstract class Middleware
    {
        public abstract function handle(Request $request);
    }