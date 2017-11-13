<?php
/*
*   Class: Events 
*   
*   Model a Event on the system. Support loadind in event data from 
*   the database, adding new, changing and deleting event data.
*/

include_once('/usr/home/user1/public_html/Booker/server/utils/Rest.php');

Class Events extends Rest
    {
        //  Connect to DB
        private $db = null;
        public function __construct()
        {
            $this->db=DateBase::getInstance();
        }

        // Get All events data from DB
        public function getEvents()
        {
            $sth = $this->db->query("SELECT e.id, u.login, u.id as id_user, r.id as room_id, r.name as room_name, 
                description, time_start as start, time_end as end, parent, time_create
                FROM booker_events as e
                LEFT JOIN booker_users AS u ON e.user_id = u.id
                LEFT JOIN booker_rooms AS r ON e.room_id = r.id
                ORDER BY time_start ASC");

            if ($sth)
            {
                $result = $sth->fetchAll(PDO::FETCH_ASSOC);
                return $this->getResponse($result, 200);
            }
            else
                return $this->getResponse(ERR_003, 404);
        }

        // Add new event in system
        public function postEvents($params)
        {
            if (Validator::checkParams($params, array('id_user', 'id_room', 'description', 'time_start', 'time_end')))
            {
                $idUser = Validator::checkId($params['id_user']) ? $params['id_user'] : false;
                $idRoom = Validator::checkId($params['id_room']) ? $params['id_room'] : false;
                $description = Validator::clearData($params['description']);
                $timeStart = Validator::clearData(explode(',', $params['time_start']));
                $timeEnd = Validator::clearData(explode(',', $params['time_end']));
                $timeCreated = Validator::clearData($params['time_created']);

                if(count($timeStart) == count($timeEnd) && $timeEnd[0] > $timeStart[0])
                {
                    $success = 0;
                    $parent = 0;

                    for($i=0; $i<=count($timeStart)-1; $i++)
                    {
                        $timeCreated = ($timeCreated && $timeCreated != '') ? $timeCreated : time();
                        $param['idRoom'] = $idRoom;
                        $uncross = $this->checkTime($timeStart[$i], $timeEnd[$i], $param);

                        if ($uncross === true)
                        {
                            $sth = $this->db->prepare("INSERT INTO booker_events 
                                (user_id, room_id, description, time_start, time_end, parent, time_create)
                                VALUES ('$idUser', '$idRoom', '$description', '$timeStart[$i]', '$timeEnd[$i]', '$parent', '$timeCreated')");

                            if (!$sth->execute())
                            {
                                return $this->getResponse(ERR_003, 404);
                                exit;
                            }
                            else
                                $parent = $parent != 0 ? $parent : $this->db->lastInsertId();
                        }
                        else
                        {
                            return $this->getResponse($i . ERR_107, 200);
                            exit;                       
                        }   
                    }

                    //  Exit from the loop
                    return $this->getResponse($i . ERR_107, 200);
                }
                else
                    return $this->getResponse(ERR_002, 404);
            }
            else
                return $this->getResponse(ERR_001, 404);       
        }

        // Edit event in system. A method can edit one event or all events in a chain by a recursive method.
        public function putEvents($params)
        {
            if (Validator::checkParams($params, array('id_event','time_start', 'time_end')))
            {
                $idEvent = Validator::checkId($params['id_event']) ? $params['id_event'] : false;
                $idUser = Validator::checkId($params['id_user']) ? $params['id_user'] : false;
                $timeStart = is_int(intval($params['time_start'])) === true ? $params['time_start'] : false;
                $timeEnd = is_int(intval($params['time_end'])) === true ? $params['time_end'] : false;
                $description = Validator::clearData($params['description']);
                $applyFlag = $params['apply_flag'];

                if($idEvent && $idUser && $timeStart && $timeEnd && $description)
                {
                    //  Intersection search
                    $param['idEvent'] = $idEvent;
                    $uncross = $this->checkTime($timeStart, $timeEnd, $param);

                    if ($uncross === true)
                    {
                        //  Recursive action selected
                        if($applyFlag == 'true')
                        {
                            //  Identify the parent element
                            $sth = $this->db->query("SELECT parent 
                                FROM booker_events
                                WHERE id = '$idEvent'");

                            $result = $sth->fetch(PDO::FETCH_ASSOC);                        
                            $idEvent = ($result['parent'] != 0) ? $result['parent'] : $idEvent; 

                            //  Finding recursive events 
                            $sth = $this->db->query("SELECT time_start, time_end 
                                FROM booker_events
                                WHERE id = '$idEvent' OR parent = '$idEvent'
                                ORDER BY id ASC");

                            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
                            $now = time();
                            $arrayReject = [];

                            //  Apply the delta to each event 
                            foreach($result as $event)
                            {
                                if(abs($timeStart - $event['time_start']) < 86400) // Zero change element
                                {
                                    $newStart = $timeStart;
                                    $newEnd = $timeEnd;
                                }
                                else
                                {
                                    $deltaWeek = round(($event['time_start'] - $timeStart) / 604800); // Number of weeks between events in the chain
                                    $deltaUtc = 604800 * abs($deltaWeek);

                                    $newStart = $event['time_start'] > $timeStart ? $timeStart + $deltaUtc : $timeStart - $deltaUtc;
                                    $newEnd = $event['time_end'] > $timeEnd ? $timeEnd+$deltaUtc : $timeEnd-$deltaUtc;
                                }

                                //  Intersection search
                                $param['idEvent'] = $idEvent;
                                $uncross = $this->checkTime($newStart, $newEnd, $param);                        

                                //  Checking each chain event on the intersection
                                if ($uncross === true)
                                {
                                    $query = "UPDATE booker_events 
                                        SET user_id = '$idUser', 
                                        description = '$description', 
                                        time_start = '$newStart', 
                                        time_end = '$newEnd'
                                        WHERE time_start = '$event[time_start]'
                                        AND (parent = '$idEvent'
                                        OR id = '$idEvent') 
                                        AND  $event[time_start] > '$now'"; // The events that have not yet come

                                    $sth = $this->db->prepare($query);
                                    $sth->execute();
                                }
                                else
                                    array_push($arrayReject, array('start'=>$newStart, 'end'=>$newEnd));
                            }

                            return $this->getResponse($arrayReject, 200);
                        }
                        //  Action for one event 
                        else
                        {
                            $query = "UPDATE booker_events 
                                SET user_id = '$idUser', description = '$description', time_start = '$timeStart', time_end = '$timeEnd' 
                                WHERE id = '$idEvent'";                   

                            $sth = $this->db->prepare($query);

                            if($sth->execute())
                                return $this->getResponse($arrayReject, 200);
                            else
                                return $this->getResponse(ERR_003, 404);
                        }
                    }
                    else
                        return $this->getResponse(ERR_106, 200); 
                }
                else
                    return $this->getResponse(ERR_002, 404);           
            }
            else
                return $this->getResponse(ERR_001, 404);  
        }

        // Search events by params: id event, id user.
        public function getEventsByParams($paramsUrl)
        {
            list ($params['id'], $params['id_user']) = explode('/', $paramsUrl, 2);
            $params = Validator::clearData($params);
            $idEvent = Validator::checkId($params['id']) ? $params['id'] : false;
            $idUser = Validator::checkId($params['id_user']) ? $params['id_user'] : false;
            $parent = $params['parent'];

            if ($idEvent || $idUser)
            {
                if ($idEvent)
                    $where = "WHERE e.id = '" . $idEvent . "'";
                else
                    $where = "WHERE e.user_id = '" . $idUser . "'";

                $sth = $this->db->query("SELECT e.id, u.login, u.id as id_user, r.name, description, time_start, time_end, parent, time_create
                    FROM booker_events as e
                    LEFT JOIN booker_users AS u ON e.user_id = u.id
                    LEFT JOIN booker_rooms AS r ON e.room_id = r.id
                    $where");

                if ($sth->rowCount()>0)
                {
                    $result = $sth->fetch(PDO::FETCH_ASSOC);

                    //  Parent check
                    if ($result['parent'] == 0)
                    {
                        $sth = $this->db->query("SELECT id FROM booker_events WHERE parent = '$result[id]'");
                        $result['parent'] = ($sth->rowCount() > 0) ? $result['id'] : false; 
                    }

                    return $this->getResponse($result, 200);
                }
                else
                    return $this->getResponse(ERR_108, 404);

            }
            else
                return $this->getResponse(ERR_001, 404);
        }

        // Delete events by id event, by recursive method at id event and by recursive method at id user .
        public function deleteEvents($params)
        {
            list($idEvent, $flag, $idUser) = explode('/', $params, 3);

            $idEvent = Validator::checkId($idEvent) ? $idEvent : false;
            $flagApply = ($flag && $flag != '') ? $flag : false;
            $idUser = Validator::checkId($idUser) ? $idUser : false;
            $now = time();

            if ($idEvent || $idUser)
            {
                if ($idEvent && $flagApply == 'true' )
                {
                    $sth = $this->db->query("SELECT parent FROM booker_events WHERE id = '$idEvent'");
                    $result = $sth->fetch(PDO::FETCH_ASSOC);

                    $idEvent = $result['parent'] != 0 ? $result['parent'] : $idEvent;                   
                    $where = "WHERE id = '$idEvent' or parent = '$idEvent'";
                }
                else if ($idUser)
                {
                    $where = "WHERE user_id = '$idUser'";
                }
                else
                    $where = "WHERE id = '$idEvent'";

                $sth = $this->db->prepare("DELETE FROM booker_events $where AND time_start > '$now'");
                $sth->execute();

                if ($sth->rowCount() > 0)
                    return $this->getResponse(ERR_004, 200);
                else
                    return $this->getResponse(ERR_108, 200);
            }
            else
                return $this->getResponse(ERR_001, 404);
        }
        
        //  Intersection search by timeline, in param can be key id room or id event
        private function checkTime($timeStart, $timeEnd, $param){

            if(!$timeStart && !$timeEnd)
                return false;
            
            if($param['idRoom'])
            {
                $sth = $this->db->query("SELECT id
                FROM booker_events as e
                WHERE ((e.time_start >= '$timeStart' AND e.time_start <= '$timeEnd')
                OR (e.time_start <= '$timeStart' AND e.time_end >= '$timeEnd')
                OR (e.time_end >= '$timeStart' AND e.time_end <= '$timeEnd'))
                AND room_id = '$param[idRoom]'");
            }
            elseif($param['idEvent'])
            {  
                $sth = $this->db->query("SELECT id FROM (SELECT id, room_id, parent
                FROM booker_events as e
                WHERE ((e.time_start >= '$timeStart' AND e.time_start <= '$timeEnd')
                OR (e.time_start <= '$timeStart' AND e.time_end >= '$timeEnd') 
                OR (e.time_end >= '$timeStart' AND e.time_end <= '$timeEnd'))
                AND room_id = (SELECT room_id FROM booker_events WHERE id = '$param[idEvent]')
                AND id <> '$param[idEvent]') as p WHERE p.parent <> '$param[idEvent]'");
            }
            return ($sth->rowCount() == 0) ? true : false;
        }
    }

$obj = new Events();
$obj->start();
