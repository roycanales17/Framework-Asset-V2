<?php

    namespace app\Artisan\Make;

    class Exception {

        protected mixed $cmd;

        function __construct( $artisan, $args )
        {
            if (!$args) {
                $artisan->title( 'ERROR', 31 );
                $artisan->info( "Custom exception name is required." );
                return;
            }

            $this->cmd = $artisan;
            $this->create( $args );
        }

        function create( string $name ): bool
        {
            $name = str_replace( '/', '\\', $name );
            $exploded = explode( '\\', $name );
            $class_name = ucfirst( $exploded[ count( $exploded ) - 1 ] );
            $namespace = "";

            if ( count( $exploded ) !== 1 )
            {
                array_pop( $exploded );
                $namespace .= "\\".implode( '\\', $exploded );
            }

            $content 	 =	'<?php'.PHP_EOL.PHP_EOL;
            $content 	.=	"\tnamespace Http\Model$namespace;".PHP_EOL.PHP_EOL;

            $content    .=  "\tuse Exception;".PHP_EOL;
            $content    .=  "\tuse App\Request;".PHP_EOL;
            $content    .=  "\tuse App\Scheme\Throwable;".PHP_EOL.PHP_EOL;

            $content 	.=	"\tclass $class_name extends Throwable".PHP_EOL;
            $content 	.=	"\t{".PHP_EOL;

            $content 	.=	"\t\tpublic function __construct(\$message = '', \$code = 0, Exception \$previous = null) {".PHP_EOL.PHP_EOL;
            $content 	.=	"\t\t\tparent::__construct(\$message, \$code, \$previous);".PHP_EOL;
            $content 	.=	"\t\t}".PHP_EOL.PHP_EOL;

            $content 	.=	"\t\tpublic function render(Request \$request): bool|string {".PHP_EOL.PHP_EOL;
            $content 	.=	"\t\t\treturn false;".PHP_EOL;
            $content 	.=	"\t\t}".PHP_EOL;

            $content 	.=	"\t}";

            $file_name = "$class_name.php";
            $directory = root."\\http\\Exceptions";

            if ($namespace) {
                $directory .= "$namespace";
            }

            $directory = str_replace( '\\', '/', $directory );
            if ( !file_exists( $directory ) )
            {
                mkdir( $directory, 0755, true );
                $file = fopen( "$directory/$file_name", 'w' ) or die( 'Cannot open file: ' . $file_name );
                fwrite( $file, $content );
                fclose( $file );
            }
            else
            {
                if ( file_exists( "$directory/$file_name" ) )
                {
                    $this->cmd->title( 'ERROR', 31 );
                    $this->cmd->info( "Already exist with the given path ($directory/$file_name)." );
                    return false;
                }

                $file = fopen( "$directory/$file_name", 'w' ) or die( 'Cannot open file: ' . $file_name );
                fwrite( $file, $content );
                fclose( $file );
            }


            $this->cmd->title( 'SUCCESS', 32 );
            $this->cmd->info( "Successfully created, given path ($directory/$file_name)." );
            return true;
        }
    }