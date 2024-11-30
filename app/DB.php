<?php

    namespace app;

    use App\Database\Connection;
    use app\Database\Eloquent;

    class DB
    {
        public static function table(string $table): Eloquent {
            return (new Eloquent())->table($table);
        }

        public static function run(string $query, array $binds = []): Connection {
            return new Connection($query, $binds);
        }
    }