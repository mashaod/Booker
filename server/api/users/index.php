<?php
/*
*   Class: Users 
*   
*   Model a User on the system. Support loadind in user data from 
*   the database, adding new, changing user data.
*/

include_once('/usr/home/user1/public_html/Booker/server/utils/Rest.php');

Class Users extends Rest
    {
        //  Connect to DB
        private $db = null;

        public function __construct()
        {
            $this->db=DateBase::getInstance();
        }

        //  Get all users from DB
        public function getUsers()
        {
            $query = "SELECT id, login, role, status, email FROM booker_users WHERE status = '1'";
            $sth = $this->db->query($query);
            if($sth)
            {
                $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                return $this->getResponse($data, 200);
            }
            else
                return $this->getResponse(ERR_003, 404);
        }

        // Add new user in system
        public function postUsers($params)
        {
            $login =  Validator::checkLogin($params['login'])? $params['login'] : false;
            $password = Validator::checkPassword($params['password'])? password_hash($params['password'], PASSWORD_BCRYPT):false;
            $email = Validator::checkEmail($params['email'])? $params['email'] : false;
            $role = $params['role']==1?1:2;

            if($login && $password && $email)
            {
                //Check login in system
                $query = "SELECT id from `booker_users` where login = '$login'";
                $sth = $this->db->query($query);

                if($sth->fetchColumn() == 0)
                {
                    // Creating hash and time now
                    $hash =  md5(mt_rand());
                    $time = strtotime("now");

                    $query = "INSERT INTO booker_users (login, email, password, role, hash, time) VALUES ('$login', '$email', '$password', '$role', '$hash', '$time')";
                    $sth = $this->db->prepare($query);

                    if($sth->execute())
                        return $this->getResponse(ERR_004, 200);
                    else
                        return $this->getResponse(ERR_003, 404);               
                }
                else
                    return $this->getResponse(ERR_104, 404);
            }
            else
                return $this->getResponse(ERR_001, 404);
        }

        //  Edit user data in DB
        public function putUsers($params)
        {
            //  Edit active user (instead delete)  
            if($params['user_status'] && $params['user_status'] == 'disabled' && $params['id_user'])
            {
                $idUser = $params['id_user'];

                //  Check by last admin
                $sth = $this->db->query("SELECT id FROM booker_users WHERE role = 1 AND status = 1 AND
                                            (SELECT role FROM booker_users WHERE id = '$idUser') = 1");
                if($sth->rowCount() == 1)
                    return $this->getResponse(ERR_105, 200); 

                //  Disabling the user
                $query = "UPDATE booker_users SET status = '0' WHERE id = '$idUser'";
                $sth = $this->db->prepare($query);

                if($sth->execute())
                    return $this->getResponse(ERR_004, 200);
                else
                    return $this->getResponse(ERR_003, 404);
            }
            
            //  Edit data user
            if(Validator::checkParams($params, array('id_user', 'login', 'email')))
            {
                $idUser = Validator::checkId($params['id_user']);
                $login = Validator::checkLogin($params['login']) ? $params['login'] : false;
                $emailStr = str_replace('%40', '@', $params['email']);
                $email = Validator::checkEmail($emailStr) ? $emailStr : false;

                if($idUser && $login && $email)
                {
                    // Check new login and email in system
                    $query = "SELECT id FROM booker_users as u WHERE (u.login = '$login' OR u.email = '$email') AND (u.id <> '$idUser') AND (u.status = '1')";       
                    $sth = $this->db->query($query);

                    if($sth->rowCount() == 0)
                    {
                        $query = "UPDATE booker_users SET login = '$login', email = '$email' WHERE id = '$idUser'";
                        $sth = $this->db->prepare($query);

                        if($sth->execute())
                            return $this->getResponse(ERR_004, 200);
                        else
                            return $this->getResponse(ERR_003, 404);
                    }
                    else
                        return $this->getResponse($login, 200); //ERR_106
                }
                else
                   return $this->getResponse(ERR_002, 200); //ERR_002
            }
            else
                return $this->getResponse(ERR_001, 404); 
        }

        // Get users by params 
        public function getUsersByParams($paramsUrl)
        {
            list($idUser, $active) = explode('/', $paramsUrl, 2);
            $idUser = Validator::checkId($idUser)? $idUser : false;

            if ($idUser && $$idUser != '-')
                $sth = $this->db->prepare("SELECT id, role, login, email from `booker_users` where id = '$idUser'");
            elseif ($active && $active == 'all')
                $sth = $this->db->prepare("SELECT id, role, login, email from `booker_users`");
            else
                return $this->getResponse(ERR_001, 404);
                
            if ($sth->execute())
            {
                $dataUser = ($sth->rowCount() > 1) ? $sth->fetchAll(PDO::FETCH_ASSOC) : $sth->fetch(PDO::FETCH_ASSOC);
                return $this->getResponse($dataUser, 200);
            }
            else
                return $this->getResponse(ERR_003, 404);               
        }
    }

$obj = new Users();
$obj->start();

