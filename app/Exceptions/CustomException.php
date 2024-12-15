<?php

    namespace app\Exceptions;

    use Core\Request;
	use Scheme\Throwable;
	use Exception;
	
	class CustomException extends Throwable
    {
        public function __construct($message = "", $code = 0, Exception $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }

        public function render(Request $request): bool|string
        {
            return false;
        }
    }
