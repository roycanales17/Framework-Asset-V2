<?php

    namespace App;
    use Error;

    class Logger
    {

        protected string $logFilePath;
        protected string $logLevel;

        const LEVEL_INFO = 'INFO';
        const LEVEL_WARNING = 'WARNING';
        const LEVEL_ERROR = 'ERROR';

        public
        static function path(string $filename): self
        {
            return new self($filename);
        }

        function __construct(string $path, string $logLevel = self::LEVEL_INFO)
        {
            $this->logLevel = $logLevel;
            $this->logFilePath = $this->buildPath($path);

            return $this;
        }

        public
        function setLogLevel($logLevel = self::LEVEL_INFO): self
        {
            $this->logLevel = $logLevel;
            return $this;
        }

        public
        function info(string $message): void
        {
            if ($this->logLevel === self::LEVEL_INFO) {
                $this->writeLog(self::LEVEL_INFO, $message);
            }
        }

        public
        function warning(string $message): void
        {
            if (in_array($this->logLevel, [self::LEVEL_INFO, self::LEVEL_WARNING])) {
                $this->writeLog(self::LEVEL_WARNING, $message);
            }
        }

        public
        function error(string $message): void
        {
            $this->writeLog(self::LEVEL_ERROR, $message);
        }

        public
        function exception($exception): void
        {
            $dateTime = new \DateTime();
            $divider = str_repeat('-', 80);

            $formattedMessage = sprintf(
                "|\n|\n|\n%s\n" .
                "[EXCEPTION LOG - %s] [%s]\n" .
                "%s\n\n" .
                "%-10s : %s\n" .
                "%-10s : %s\n" .
                "%-10s : %d\n" .
                "\nStack Trace:\n%s\n\n%s\n",
                $divider,
                $dateTime->format('Y-m-d H:i:s'),
                strtoupper('ERROR'),
                $divider,
                'Message', $exception->getMessage(),
                'File', $exception->getFile(),
                'Line', $exception->getLine(),
                $exception->getTraceAsString(),
                $divider
            );

            file_put_contents($this->logFilePath, $formattedMessage, FILE_APPEND);
        }

        private
        function writeLog(string $level, string $message): void
        {
            $dateTime = new \DateTime();
            $formattedMessage = sprintf(
                "[%s] [%s]: %s\n",
                $dateTime->format('Y-m-d H:i:s'),
                strtoupper($level),
                $message
            );

            file_put_contents($this->logFilePath, $formattedMessage, FILE_APPEND);
        }

        private
        function buildPath(string $path): string
        {
            $path = root . "/logger/$path";
            $this->createFile($path);
            return $path;
        }

        private
        function createFile(string $logFilePath): void
        {
            $directoryPath = dirname($logFilePath);

            if (!file_exists($directoryPath)) {

                if (!mkdir($directoryPath, 0777, true)) {
                    if (config('development')) {
                        die("Failed to create directory: $directoryPath");
                    }
                }

                chmod($directoryPath, 0777);
            }

            if (!file_exists($logFilePath)) {
                touch($logFilePath);
            }
        }
    }