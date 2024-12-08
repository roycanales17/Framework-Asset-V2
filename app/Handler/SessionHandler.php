<?php

    namespace app\Handler;

    use app\Database\DB;
    use app\Database\SQL\Blueprint;
    use app\Database\SQL\Schema;
    use app\Session;
    use SessionHandlerInterface;

    class SessionHandler implements SessionHandlerInterface
    {
        protected string $table = 'session';

        function __construct()
        {
            $alreadyExist = false;
            if (Session::started() && Session::has('SCHEMA_EXIST')) {
                $alreadyExist = true;
            }

            if (!$alreadyExist && !Schema::hasTable($this->table)) {
                Schema::create($this->table, function(Blueprint $col) {
                    $col->string('id', 128)->primary();
                    $col->blob('data');
                    $col->string('ip', 64);
                    $col->dateTime('expires_at');
                });
                Session::set('SCHEMA_EXIST', true);
            }
        }

        public function open($path, $name): bool
        {
            // Nothing needed here for now
            return true;
        }

        public function close(): bool
        {
            // Nothing needed here for now
            return true;
        }

        public function read($id): string|false
        {
            // Fetch session data from the database
            $data = db::table($this->table)
                ->select('data')
                ->where('id', $id)
                ->field();

            // If session data found, decrypt and return it, else create an empty session
            if ($data) {
                return decrypt($data);
            }

            // If no session data, create an empty session and return an empty string
            $this->write($id, '');
            return false;
        }

        public function write($id, $data): bool
        {
            // Set session expiration time
            $expires_at = date('Y-m-d H:i:s', time() + config('SESSION_LIFETIME'));

            // Insert or update session data in the database
            return (bool) db::run("REPLACE INTO $this->table (`id`, `data`, `ip`, `expires_at`) VALUES ( :session_id, :data, :ip, :expires_at )", [
                'session_id' => $id,
                'data'       => encrypt($data),
                'ip'         => IPAddress(),
                'expires_at' => $expires_at
            ])->getAffectedRows();
        }

        public function destroy($id): bool
        {
            // Delete the session from the database
            return (bool) db::run("DELETE FROM $this->table WHERE id = :id", ['id' => $id])->getAffectedRows();
        }

        public function gc($max_lifetime): int|false
        {
            // Calculate the expiration date based on max_lifetime
            $expiration = date('Y-m-d H:i:s', time() - $max_lifetime);

            // Clean up expired sessions
            return (bool) db::run("DELETE FROM $this->table WHERE expires_at < :expiration", [
                'expiration' => $expiration
            ])->getAffectedRows();
        }

        public function session_close(): void
        {
            // Close the session and write it
            session_write_close();
        }
    }
