<?php

    namespace app\Artisan\Make;

    class Middleware {

        protected mixed $cmd;

        function __construct( $artisan, $args )
        {
            if (!$args) {
                $artisan->title( 'ERROR', 31 );
                $artisan->info( "Middleware name is required." );
                return false;
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
            $content 	.=	"\tnamespace http\Middleware$namespace;".PHP_EOL.PHP_EOL;

            $content    .=  "\tuse app\Request;".PHP_EOL;
            $content    .=  "\tuse App\Scheme\Middleware;".PHP_EOL.PHP_EOL;

            $content 	.=	"\tclass $class_name extends Middleware".PHP_EOL;
            $content 	.=	"\t{".PHP_EOL;

            $content 	.=	"\t\tpublic function handle(Request \$request): bool {".PHP_EOL.PHP_EOL;
            $content 	.=	"\t\t\treturn true;".PHP_EOL;
            $content 	.=	"\t\t}".PHP_EOL.PHP_EOL;

            $content 	.=	"\t}";

            $file_name = "$class_name.php";
            $directory = root."\\http\\Middleware";

            if ( $namespace ) {
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