<?php

class App
{
    /** @var string  */
    const ERROR_LOG = 'var/error.log';

    /**
     * App constructor.
     */
    public function __construct()
    {
        set_error_handler(array($this, 'errorHandler'), -1);
        set_exception_handler(array($this, 'exceptionHandler'));
        register_shutdown_function(array($this, 'shutDown'));
    }

    /**
     * It main entry point, it literally run application
     */
    public function run()
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
    public function errorHandler($errorNo, $errorMessage, $errorFile, $errorLine)
    {
        $error = 'Error level: ' . $errorNo . ' Text: ' . $errorMessage . ' in file: ' . $errorFile . ' on line: ' . $errorLine . "\n";
        error_log($error, 3, $_SERVER['DOCUMENT_ROOT'] . self::ERROR_LOG);
    }

    /** Write fatal error to log file and show error page
     */
    public function shutDown()
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
    public function exceptionHandler($e)
    {
        error_log($e->getMessage(), 3, $_SERVER['DOCUMENT_ROOT'] . self::ERROR_LOG);
        require($_SERVER['DOCUMENT_ROOT'] . 'view/error.php');
    }
}