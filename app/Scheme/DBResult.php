<?php

    namespace App\Scheme;

    /**
     * Class DBResult
     * This abstract class provides a set of methods for handling and processing
     * database query results for both PDO and MySQLi connections.
     */
    abstract class DBResult
    {
        protected static \PDO|null $pdoConnection = null;
        protected static \mysqli|null $mySqliConnection = null;
        protected mixed $result = null;
        protected string $connectionType;

        /**
         * Returns the active database connection (either PDO or MySQLi).
         *
         * @return \PDO|\mysqli|null
         */
        private function link(): \PDO|\mysqli|null {
            return $this->connectionType === 'pdo' ? self::$pdoConnection : self::$mySqliConnection;
        }

        /**
         * Returns the last inserted ID for the current database connection.
         *
         * @return int The last inserted ID, or 0 if no valid connection is available.
         */
        public function lastID(): int {
            $connection = $this->link();
            if ($connection instanceof \PDO) {
                return (int) $connection->lastInsertId();
            } elseif ($connection instanceof \mysqli) {
                return (int) $connection->insert_id;
            }
            return 0;
        }

        /**
         * Returns the result set as an array.
         *
         * @return array The result set as an array, or an empty array if the result is not an array.
         */
        public function fetch(): array {
            return is_array($this->result) ? $this->result : [];
        }

        /**
         * Returns a column from the result set.
         *
         * @return array An array containing the values of the first column, or an empty array if no result.
         */
        public function col(): array {
            if (is_array($this->result) && !empty($this->result)) {
                return array_column($this->result, array_key_first($this->result[0]));
            }
            return [];
        }

        /**
         * Returns the first field of the first row from the result set.
         *
         * @return mixed The first field of the first row, or null if no result exists.
         */
        public function field(): mixed {
            if (is_array($this->result) && !empty($this->result)) {
                return reset($this->result[0]);
            }
            return null;
        }

        /**
         * Returns the first row from the result set.
         *
         * @return array The first row, or an empty array if no result exists.
         */
        public function row(): array {
            return is_array($this->result) && !empty($this->result) ? $this->result[0] : [];
        }

        /**
         * Returns the number of rows in the result set.
         *
         * @return int The number of rows in the result set, or 0 if there are no results.
         */
        public function count(): int {
            return is_array($this->result) ? count($this->result) : (int) $this->result;
        }

        /**
         * Checks if the result set contains any data.
         *
         * @return bool True if the result set is not empty, otherwise false.
         */
        public function exists(): bool {
            return !empty($this->result);
        }

        /**
         * Returns the first element in the result set.
         *
         * @return array|null The first row of the result set, or null if no result exists.
         */
        public function first(): ?array {
            return !empty($this->result) ? $this->result[0] : null;
        }

        /**
         * Returns the last element in the result set.
         *
         * @return array|null The last row of the result set, or null if no result exists.
         */
        public function last(): ?array {
            return !empty($this->result) ? end($this->result) : null;
        }

        /**
         * Returns the raw result set.
         *
         * @return mixed The raw result set.
         */
        public function raw(): mixed {
            return $this->result;
        }

        /**
         * Converts the result set to a JSON string.
         *
         * @return string The result set as a JSON string.
         * @throws \JsonException If encoding the result set as JSON fails.
         */
        public function toJson(): string {
            return json_encode($this->result, JSON_THROW_ON_ERROR);
        }

        /**
         * Returns the number of affected rows for the current query.
         *
         * @return int The number of affected rows, or 0 if no valid connection is available.
         */
        public function getAffectedRows(): int {
            $connection = $this->link();
            if ($connection instanceof \PDO) {
                return $connection->rowCount();
            } elseif ($connection instanceof \mysqli) {
                return $connection->affected_rows;
            }
            return 0;
        }

        /**
         * Returns the last error message from the current database connection.
         *
         * @return string The error message, or an empty string if no error exists.
         */
        public function getError(): string {
            $connection = $this->link();
            if ($connection instanceof \PDO) {
                return $connection->errorInfo()[2] ?? '';
            } elseif ($connection instanceof \mysqli) {
                return $connection->error ?? '';
            }
            return '';
        }

        /**
         * Returns the column names from the result set.
         *
         * @return array The column names from the result set, or an empty array if no result is found.
         */
        public function getColumns(): array {
            if ($this->result instanceof \PDOStatement) {
                return array_keys($this->result->fetch(\PDO::FETCH_ASSOC));
            } elseif ($this->result instanceof \mysqli_result) {
                return array_keys($this->result->fetch_assoc());
            }
            return [];
        }
    }
