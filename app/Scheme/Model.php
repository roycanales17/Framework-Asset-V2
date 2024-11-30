<?php

    namespace app\Scheme;

    use app\Database\Blueprint;
    use app\Database\Eloquent;

    /**
     * Class Model
     * Provides methods for interacting with database records in an object-oriented manner.
     * This class extends the Blueprint class and is intended to be extended by concrete models.
     */
    abstract class Model extends Blueprint
    {
        /**
         * Retrieves all records from the associated table.
         *
         * @return array Returns an array of all records from the table.
         */
        public static function all(): array
        {
            $object = self::object([
                'primary_key',
                'fillable'
            ]);

            $obj = new Eloquent();
            return $obj->select( '*' )->table( $object->getTable() )->fetch();
        }

        /**
         * Creates a new record in the associated table with the provided data.
         *
         * @param array $binds The data to be inserted into the table.
         *
         * @return int The ID of the last inserted record.
         */
        public static function create(array $binds): int
        {
            $object = self::object([
                'primary_key',
                'fillable'
            ]);

            $query = new Eloquent();
            return $query->create($binds, $object->getFillable(), $object->getTable());
        }

        /**
         * Replaces an existing record or inserts a new record if no matching record exists.
         *
         * @param array $binds The data to be inserted or replaced.
         *
         * @return int The ID of the last inserted or replaced record.
         */
        public static function replace(array $binds): int
        {
            $object = self::object([
                'primary_key',
                'fillable'
            ]);

            $query = new Eloquent();
            return $query->replace($binds, $object->getFillable(), $object->getTable());
        }

        /**
         * Finds a record by its primary key.
         *
         * @param int $id The primary key of the record to find.
         *
         * @return array Returns the record as an array or an empty array if not found.
         */
        public static function find(int $id): array
        {
            $obj = new Eloquent();
            $object = self::object([
                'primary_key',
                'fillable'
            ]);

            $obj->select("*");
            $obj->table($object->getTable());
            $obj->where($object->getPrimary(), $id);

            return $obj->row();
        }

        /**
         * Prepares a query for selecting specific columns from the associated table.
         *
         * @return Eloquent Returns an instance of the Eloquent query builder with the table set.
         */
        public static function select(): Eloquent
        {
            $object = self::object([
                'primary_key',
                'fillable'
            ]);

            $query = new Eloquent();
            foreach (func_get_args() as $column)
                $query->select($column);

            return $query->table($object->getTable());
        }

        /**
         * Adds a WHERE clause to the query to filter records based on a column and value.
         *
         * @param string $column The column to filter by.
         * @param mixed $operator_or_value The operator to use for comparison, or a direct value for equality.
         * @param mixed $value The value to compare against if an operator is provided (optional).
         *
         * @return Eloquent Returns an instance of the Eloquent query builder with the WHERE condition applied.
         */
        public static function where(string $column, mixed $operator_or_value, mixed $value = self::DEFAULT_VALUE): Eloquent
        {
            $obj = new Eloquent();
            $object = self::object([
                'primary_key',
                'fillable'
            ]);

            $obj->table($object->getTable());
            $obj->where($column, $operator_or_value, $value);

            return $obj;
        }
    }