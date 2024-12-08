<?php

    namespace app\Database;

    class DB
    {
        public static function table(string $table): Eloquent {
            return (new Eloquent)->table($table);
        }

        public static function run(string $query, array $binds = []): Connection {
            return new Connection($query, $binds);
        }
    }