<?php

    namespace app\Database\SQL;

    use app\Database\DB as Database;

    class Schema
    {
        static function dropIfExists(string $table): void
        {
            Database::run("
                IF EXISTS ( SELECT * FROM information_schema.tables WHERE table_schema = '".config('DB_NAME', 'framework')."' AND table_name = '$table' )
                THEN
                    DROP TABLE $table;
                END IF;"
            );
        }

        static function hasTable(string $table): bool {
            return ( bool )Database::run("SHOW TABLES LIKE '$table'")->first();
        }

        static function create(string $table, \Closure $callback): void
        {
            # Initialize
            $instance = new Blueprint($table);
            $callback($instance);

            # Run SQL
            Database::run(Builder::compile());
        }
    }