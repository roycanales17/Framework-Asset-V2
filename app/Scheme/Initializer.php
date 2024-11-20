<?php

    namespace app\Scheme;

    Interface Initializer {

        public function onLoad(): void;

        public function onExit(): void;
    }