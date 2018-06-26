<?php

class Log
{
    const LOG_FILE = 'var/error.log';

    public function write($error)
    {
        error_log($error, 3, $_SERVER['DOCUMENT_ROOT'] . self::LOG_FILE);
    }
}