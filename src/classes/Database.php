<?php

final class Database
{
    const CONFIG_FILE = 'var/config.ini';
    /**
     * @var
     */
    private static $pdo;

    /**
     * gets the PDO via lazy initialization (created on first usage)
     */
    public static function connect()
    {
        if (null === static::$pdo) {
            try {
                if (file_exists(self::CONFIG_FILE)) {
                    $config = parse_ini_file(self::CONFIG_FILE);
                    static::$pdo = new PDO(
                        $config['engine'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'], $config['user'], $config['pass']);
                } else {
                    throw new Exception('Config file is not exist');
                }
            } catch (PDOException $exception) {
                App::get('log')->write($exception->getMessage());
                echo 'Could not connect to DB';
                exit;
            } catch (Exception $exception) {
                App::get('log')->write($exception->getMessage());
                echo 'Could not connect to DB';
                exit;
            }
        }

        return static::$pdo;
    }

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    private function __wakeup()
    {
    }
}