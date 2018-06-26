<?php

abstract class DataEntity
{
    public $database;

    public function __construct()
    {
         $this->database = Database::connect();
    }

    /** Process database queries
     *
     * @param string $sql
     * @param array $params
     * @return bool|int|PDOStatement
     */
    protected function request($sql, $params = [])
    {
        $query = $this->database->prepare($sql);
        $result = $query->execute($params);
        if ($result === false) {
            error_log($query->errorInfo()[2], 3, $_SERVER['DOCUMENT_ROOT'] . Log::LOG_FILE);
            return false;
        }

        return $query;
    }

}