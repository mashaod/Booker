<?php
/*
*   Class: DateBase 
*   
*   Model a DateBase on the system. Support connect to DB and get data from 
*   the database for transmission in the model.
*/

class DateBase
{ 
    private static $db = null;

    public static function getInstance()
    {     
        if(is_null(self::$db)) 
        {
            self::$db = new PDO('mysql:host='.DB_HOST.'; dbname='.DB_DB , DB_USER, DB_PASSWORD);
        } 
        return self::$db;
    }   

    function __construct(){}
    function __clone() {}
    function __wakeup() {}
    function __destruct(){}
}
