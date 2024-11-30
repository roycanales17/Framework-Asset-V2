<?php

    namespace app\Artisan\Clear;

    class Logs {

        protected mixed $cmd;
        protected int $total_removed = 0;

        function __construct( $artisan )
        {
            $this->cmd = $artisan;
            $this->remove( root."/logger" );
        }

        function remove( string $path ): void
        {
            $this->cmd->title( '[ PROCESSING ]', 37 );

            if ( $this->remove_files( $path ) )
            {
                if ( !$this->total_removed )
                {
                    $this->cmd->title( 'ERROR', 31 );
                    $this->cmd->info( "No files were removed." );
                }
                else
                {
                    $this->cmd->title( 'SUCCESS', 32 );
                    $this->cmd->info( "A total of ".$this->total_removed." file/s is successfully removed!" );
                }
            }

            $this->total_removed = 0;
        }

        private function remove_files( string $dir = '' ): bool
        {
            $tab = "  ";
            if ( !is_dir( $dir ) )
            {
                $this->cmd->title( 'ERROR', 31 );
                $this->cmd->info( 'Directory does not exist.' );
                return false;
            }

            $files = array_diff( scandir( $dir ), ['.', '..'] );

            foreach ( $files as $file )
            {
                $path = $dir . '/' . $file;
                if ( is_dir( $path ) ) {
                    $this->remove_files( $path );
                }
                else
                {
                    $size = filesize($path);
                    if ($size !== false) {
                        echo "$tab- Deleted: Size {$size} bytes | $path\n";
                    } else {
                        echo "$tab- Deleted: Unable to determine size | $path\n";
                    }

                    if (@unlink($path)) {
                        $this->total_removed = $this->total_removed + 1;
                        echo "$tab- Successfully deleted: $path\n";
                    } else {
                        echo "$tab- Failed to delete (permission denied): $path\n";
                    }
                }
            }

            return true;
        }
    }