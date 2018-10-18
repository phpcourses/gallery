<?php

class Log
{
    const LOG_FILE = 'var/error.log';
    
    /**
	 * Write error to file
     * @param $error
     */
    public function write($error)
    {
        error_log($error, 3, $_SERVER['DOCUMENT_ROOT'] . self::LOG_FILE);
    }
}
