<?php

class App
{
    const ERROR_LOG = 'var/error.log';

    function __construct()
    {
        set_error_handler(array($this, 'errorHandler'), -1);
        set_exception_handler(array($this, 'exceptionHandler'));
        register_shutdown_function(array($this, 'shutDown'));
    }

    function run()
    {
        $page = new Page($_GET['page']);
        $page->load();
    }

    /** Write error to log file
     *
     * @param $errorNo
     * @param $errorMessage
     * @param $errorFile
     * @param $errorLine
     */
    function errorHandler($errorNo, $errorMessage, $errorFile, $errorLine)
    {
        $error = 'Error level: ' . $errorNo . ' Text: ' . $errorMessage . ' in file: ' . $errorFile . ' on line: ' . $errorLine . "\n";
        error_log($error, 3, $_SERVER['DOCUMENT_ROOT'] . self::ERROR_LOG);
    }

    /** Write fatal error to log file and show error page
     */
    function shutDown()
    {
        if ($error = error_get_last()) {
            error_log($error['message'], 3, $_SERVER['DOCUMENT_ROOT'] . self::ERROR_LOG);
            require($_SERVER['DOCUMENT_ROOT'] . 'view/error.php');
        }
    }

    /** Write exception to log and show error page
     *
     * @param $e
     */
    function exceptionHandler($e)
    {
        error_log($e->getMessage(), 3, $_SERVER['DOCUMENT_ROOT'] . self::ERROR_LOG);
        require($_SERVER['DOCUMENT_ROOT'] . 'view/error.php');
    }
}