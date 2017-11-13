<?php
/*
*   Class: Rooms 
*   
*   Model a Room on the system. Support loadind in room data from 
*   the database.
*/

include_once('/usr/home/user1/public_html/Booker/server/utils/Rest.php');

Class Rooms extends Rest
    {
        //  Connect to DB
        public $db = null;

        public function __construct()
        {
            $this->db=DateBase::getInstance(); 
        }

        //  Get all rooms from DB
        public function getRooms()
        {
            $query = "SELECT id, name FROM booker_rooms";
            $sth = $this->db->query($query);
            if($sth)
            {
                $data = $sth->fetchAll(PDO::FETCH_ASSOC); 
                return $this->getResponse($data, 200);
            }
            else
                return $this->getResponse(ERR_003, 404);
        }
    }

$obj = new Rooms();
$obj->start();

