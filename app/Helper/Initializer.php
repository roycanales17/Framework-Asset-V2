<?php

    namespace app\Helper;

    class Initializer
    {
        private string $start = '';
        private string $exit = '';

        public
        function onLoad(string $className): void
        {
            if (class_exists($className)) {
                $this->start = $className;
            }
        }

        public
        function onExit(string $className): void
        {
            if (class_exists($className)) {
                $this->exit = $className;
            }
        }

        private
        function getSetup(): array
        {
            return [
                'header' => $this->start,
                'footer' => $this->exit
            ];
        }
    }