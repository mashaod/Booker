<?php
/*
*   Class: Auth 
*   
*   Model a Auth on the system. Support loadind in auth data from 
*   the database, changing auth data.
*/

include_once('/usr/home/user1/public_html/Booker/server/utils/Rest.php');

Class Auth extends Rest
    {
        //  Connect to DB
        private $db = null;

        public function __construct()
        {
            $this->db=DateBase::getInstance();
        }

        //  Login and creating new hash of user
        public function putAuth($params)
        {
            if (count($params)>0)
            {
                $login =  Validator::checkLogin($params['login'])? $params['login'] : false;
                $password = Validator::checkPassword($params['password'])? $params['password']:false;

                if ($login && $password)
                {
                    $query = "SELECT id as id_user, password from booker_users where login = '$login'";
                    $sth = $this->db->query($query);
                    $user = $sth->fetch(PDO::FETCH_ASSOC);

                    if (count($user)>0)
                    {
                        if (password_verify($password, $user['password']))
                        {
                            $hash =  md5(mt_rand());
                            $time = time();

                            $query = "UPDATE booker_users SET hash = '$hash', time = '$time' where login = '$login'";
                            $sth = $this->db->prepare($query);
                            if ($sth->execute())
                            {
                                $data['id_user'] = $user['id_user'];
                                $data['hash'] = $hash;
                                return $this->getResponse($data, 200);
                            } 
                            else 
                                return $this->getResponse(ERR_003, 404);             
                        }
                        else
                            return $this->getResponse(ERR_102, 404);
                    }
                    else
                        return $this->getResponse(ERR_101, 404);
                }
                else
                    return $this->getResponse(ERR_002, 404);
            }         
            else
                return $this->getResponse(ERR_001, 404);
        }

        //  Check auth by id user and hash
        public function getAuthByParams($paramsUrl)
        {
            list($id_user, $hash) = explode('/', $paramsUrl, 2);
            $id_user = Validator::checkId($id_user) ? $id_user : false;

            if ($id_user && $hash && !empty($hash))
            {
                $sth = $this->db->prepare("SELECT hash, role from `booker_users` where id = '$id_user'");
                $sth->execute();

                if ($sth->execute())
                {
                    $hashInput = (strlen($hash) == 32) ? $hash : false;
                    $dataUser = $sth->fetch(PDO::FETCH_ASSOC);

                    if ($hashInput == $dataUser['hash'])
                        return $this->getResponse(ERR_004, 200);
                    else
                        return $this->getResponse(ERR_103, 404);
                }
                else
                    return $this->getResponse(ERR_003, 404); 
            }
            else
                return $this->getResponse(ERR_002, 404);               
        }
    }

$obj = new Auth();
$obj->start();

