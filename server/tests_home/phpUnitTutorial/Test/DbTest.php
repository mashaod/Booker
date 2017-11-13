<?php

class DbTest
{
	private static $db = null;

	private $toDelete = array();

	public function __construct()
	{
		if (!self::$db)
		{
			self::$db = new PDO('mysql:host='.DB_HOST.'; dbname='.DB_DB , DB_USER, DB_PASSWORD);
		}
	}

	public function addDBRecord($queryToInsert, $queryToDelete = '')
	{
		if (self::$db->exec($queryToInsert) !== false)
		{
			if ($queryToDelete)
			{
				$this->toDelete[] = $queryToDelete;
			}
		}
		else
		{
			throw new Exception('Error while insert!' . $queryToInsert);
		}
    }
    

	public function addToClear($query)
	{
		$this->toDelete[] = $query;;
	}

	public function clear()
	{
		foreach ($this->toDelete as $query)
		{
			self::$db->exec($query);
		}
	}

	public function getDBRecord($query)
	{
        $sth = self::$db->query($query);

        if ($sth)
        {
            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
        else
            throw new Exception('Invlid query!' . $query);
    }

    public function getLastId(){
        $id = self::$db->lastInsertId();
        return $id;
    }
}
