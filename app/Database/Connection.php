<?php

    namespace App\Database;

    use App\Scheme\DBResult;
    use Exception;

    /**
     * Class Connection
     * Handles database connections and queries using either PDO or MySQLi.
     */
    class Connection extends DBResult
    {
        private string $host;
        private string $database;
        private string $username;
        private string $password;
        private string $port;

        /**
         * Connection constructor.
         * Initializes the connection parameters and either runs a PDO or MySQLi query based on provided binds.
         *
         * @param string $query The SQL query to execute.
         * @param array $binds The parameters to bind to the query (only for PDO).
         * @throws Exception
         */
        public function __construct(string $query, array $binds = [])
        {
            $this->host = config('DB_HOST');
            $this->port = config('DB_PORT');
            $this->database = config('DB_NAME');
            $this->username = config('DB_USER');
            $this->password = config('DB_PASSWORD');

            if ($binds) {
                $this->PDO($query, $binds);
            } else {
                $this->MySQLi($query);
            }
        }

        /**
         * Executes a query using PDO.
         *
         * @param string $query The SQL query to execute.
         * @param array $binds The parameters to bind to the query.
         *
         * @return self Returns the instance of the class for method chaining.
         * @throws Exception If there is an error with the PDO connection or query execution.
         */
        protected function PDO(string $query, array $binds): self
        {
            $this->connectionType = 'pdo';

            $bindsValue = [];
            foreach ($binds as $key => $value) {
                if ($key[0] !== ':') {
                    $modifiedKey = ':' . $key;
                } else {
                    $modifiedKey = $key;
                }

                $bindsValue[$modifiedKey] = $value;
            }

            try {
                if (!self::$pdoConnection) {
                    $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";

                    // Open connection
                    self::$pdoConnection = new \PDO($dsn, $this->username, $this->password, [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES => false,
                        \PDO::MYSQL_ATTR_FOUND_ROWS => true,
                    ]);

                    // Set connection timeout to 180 seconds (3 minutes)
                    self::$pdoConnection->setAttribute(\PDO::ATTR_TIMEOUT, 180);
                }

                $pdo = self::$pdoConnection;
                $stmt = $pdo->prepare($query);
                $stmt->execute($bindsValue);

                if (stripos(trim($query), 'SELECT') === 0) {
                    $this->result = $stmt->fetchAll();
                } else {
                    $this->result = $stmt->rowCount();
                }

                return $this;
            } catch (\PDOException $e) {
                throw new Exception("PDO Error: " . $e->getMessage());
            }
        }

        /**
         * Executes a query using MySQLi.
         *
         * @param string $query The SQL query to execute.
         *
         * @return self Returns the instance of the class for method chaining.
         * @throws Exception If there is an error with the MySQLi connection or query execution.
         */
        protected function MySQLi(string $query): self
        {
            $this->connectionType = 'mysqli';

            try {
                if (!self::$mySqliConnection) {
                    // Open connection
                    self::$mySqliConnection = new \mysqli($this->host, $this->username, $this->password, $this->database, $this->port);

                    self::$mySqliConnection->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
                    self::$mySqliConnection->real_query("SET SESSION sql_mode='STRICT_ALL_TABLES'");

                    // Set connection timeout to 180 seconds (3 minutes)
                    self::$mySqliConnection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 180);

                    if (self::$mySqliConnection->connect_error) {
                        throw new Exception("MySQLi Connection Error: " . self::$mySqliConnection->connect_error);
                    }

                    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                }

                $mysqli = self::$mySqliConnection;
                $result = $mysqli->query($query);

                if ($result === false) {
                    throw new Exception("MySQLi Query Error: " . $mysqli->error);
                }

                if ($result instanceof \mysqli_result) {
                    $this->result = $result->fetch_all(MYSQLI_ASSOC);
                } else {
                    $this->result = $mysqli->affected_rows;
                }

                return $this;
            } catch (\mysqli_sql_exception $e) {
                throw new Exception("MySQLi Error: " . $e->getMessage());
            }
        }
    }