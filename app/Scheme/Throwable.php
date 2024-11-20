<?php

    namespace app\Scheme;

    use app\Helper\Mapping;
    use app\Helper\Skeleton;
    use Error;

    abstract class Throwable extends Error
    {
        use Mapping;
        use Skeleton;

        protected
        string $preferredIDE = 'phpstorm';
    }