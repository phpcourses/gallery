<?php

class App
{
    /** @var array fake dependency injection(singleton) */
    private static $di = [];

    /**
     * App constructor.
     */
    public function __construct()
    {
        set_error_handler(array($this, 'errorHandler'), -1);
        set_exception_handler(array($this, 'exceptionHandler'));
        register_shutdown_function(array($this, 'shutDown'));
        $this->get('session');
    }

    /**
     * It main entry point, it literally run application
     */
    public function run()
    {
        $page = new Page($_GET);
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
        App::get('log')->write($error);
    }

    /** Write fatal error to log file and show error page
     */
    public function shutDown()
    {
        if ($error = error_get_last()) {
            App::get('log')->write($error['message']);
            require($_SERVER['DOCUMENT_ROOT'] . 'view/error.php');
        }
    }

    /** Write exception to log and show error page
     *
     * @param $e
     */
    public function exceptionHandler($e)
    {
        App::get('log')->write($e->getMessage());
        require($_SERVER['DOCUMENT_ROOT'] . 'view/error.php');
    }

    /**
     * Static method for getting multiple instance of classes we use in our app
     *
     * @param $className
     * @return mixed
     */
    public static function get($className)
    {
        if (!isset(self::$di[$className])) {
            $class = ucwords($className);
            self::$di[$className] = new $class;
        }
        return self::$di[$className];
    }
}