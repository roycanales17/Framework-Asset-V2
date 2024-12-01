<?php

    namespace app\Database;

    trait DataRow
    {
        function lastID(): int {
            return $this->execute( __FUNCTION__ );
        }

        function fetch(): array {
            return $this->execute( __FUNCTION__ );
        }

        function col(): array {
            return $this->execute( __FUNCTION__ );
        }

        function field(): mixed {
            return $this->execute( __FUNCTION__ );
        }

        function row(): array {
            return $this->execute( __FUNCTION__ );
        }

        function count(): int {
            return $this->execute( __FUNCTION__ );
        }

        function exists(): bool {
            return $this->execute(__FUNCTION__);
        }
        function first(): ?array {
            return $this->execute(__FUNCTION__);
        }
        function last(): ?array {
            return $this->execute(__FUNCTION__);
        }
        function raw(): mixed {
            return $this->execute(__FUNCTION__);
        }
        function toJson(): string {
            return $this->execute(__FUNCTION__);
        }
        function getAffectedRows(): int {
            return $this->execute(__FUNCTION__);
        }
        function getError(): string {
            return $this->execute(__FUNCTION__);
        }
        function getColumns(): array {
            return $this->execute(__FUNCTION__);
        }

        function delete() {
            return $this->execute( __FUNCTION__ );
        }

        function update( array $binds )
        {
            $temp_col = null;
            foreach ( $binds as $key => $value )
            {
                $this->register_binds( $key, $value, $temp_col );
                if ( $temp_col )
                    $this->update_binds[ $temp_col ] = $value;
            }

            return $this->execute( __FUNCTION__ );
        }
    }