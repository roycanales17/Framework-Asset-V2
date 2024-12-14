<?php

    namespace app;

    use app\Requests\{Blueprint, Response};

    class Request extends Blueprint
    {
        function __construct()
        {
            $this->setInputPayload();
        }

        public function inputs(): array {
            return $this->getInput();
        }

        public function input( string $input ): mixed {
            return $this->getInput( $input );
        }

        public function query( string $input ): mixed
        {
            $get = $this->inputPayload( 'GET' );
            return $get[ strtolower( $input ) ] ?? '';
        }

        public function post( string $input ): mixed
        {
            $post = $this->inputPayload( 'POST' );
            return $post[ strtolower( $input ) ] ?? '';
        }

        public function json( string $input ): mixed
        {
            $json = $this->inputPayload( 'JSON' );
            return $json[ strtolower( $input ) ] ?? '';
        }

        public function file( string $input ): mixed
        {
            $file = $this->inputPayload( 'FILES' );
            return $file[ strtolower( $input ) ] ?? '';
        }

        public function has( string $input ): bool {
            return array_key_exists( strtolower( $input ), $this->inputs() );
        }

        public function method(): string {
            return $this->getMethod();
        }

        public function only( array $input_keys ): array
        {
            $stored = [];
            $inputs = $this->inputs();
            foreach ( array_map( 'strtolower', $input_keys ) as $key ) {
                if ( isset( $inputs[ $key ] ) ) {
                    $stored[ $key ] = $inputs[ $key ];
                }
            }

            return $stored;
        }

        public function except( array $input_keys ): array
        {
            $array = [];
            foreach ( $this->inputs() as $key => $value )
            {
                if ( !in_array( $key, array_map( 'strtolower', $input_keys ) ) ) {
                    $array[ $key ] = $value;
                }
            }

            return $array;
        }

        public function errors( bool $force_all = false ): array
        {
            if ( $force_all )
                return $this->getResponse();

            $msg = [];
            foreach ( $this->getResponse() as $res_key => $res_value ) {
                if ( !preg_match('/\[[^\]]*\]/', $res_key ) )
                    $msg[ $res_key ] = $res_value;
            }
            return $msg;
        }

        public function error( string $key ): mixed
        {
            $res = $this->errors( true );
            return $res[ $key ] ?? "";
        }

        public function isMatched( string $key, mixed $value ): bool {
            return $this->input( $key ) === $value;
        }

        public function isSuccess(): bool
        {
            $this->startValidate();
            return count( $this->getResponse() ) === 0;
        }

        public function isFailed(): bool
        {
            $this->startValidate();
            return count( $this->getResponse() ) > 0;
        }

        public function validate( array $array ): self
        {
            $this->resetProperty();
            $this->setValidateProperty( $array );
            return $this;
        }

        public function message( array $array ): void {
            $this->setMessageProperty( $array );
        }

        public function response( int $code = 200 ): Response
        {
            /**
             * 200 => 'OK',
             * 400 => 'Bad Request',
             * 401 => 'Unauthorized',
             * 403 => 'Forbidden',
             * 404 => 'Not Found',
			 * 429 => 'Too Many Requests'
             * 500 => 'Internal Server Error'
             */
            return new Response( $code );
        }

        public function params( string $input = '' ): array|string {
            if ( $input ) {
                return self::$params[ $input ] ?? '';
            }
            return self::$params;
        }
    }