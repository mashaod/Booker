<?php
include_once('../api/users/index.php');
include_once('phpUnitTutorial/Test/DbTest.php');

class UsersTest extends PHPUnit_Framework_TestCase
{
    private $model, $db;

    public function setUp()
    {
        $this->model = new Users();
        $this->db = new DbTest();
    }

    public function tearDown()
    {
        $this->db->clear();
    }

    public function testGetUsersFalse()
    {
        $result = $this->model->getUsers();
        $this->assertEquals($result, array('data'=>array(),'code'=>'200'));
    }

    public function testGetUsersTrue()
    {
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '1', '1', '1', '1', '1', '1')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";

        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser, $queryDelUser);
        $result = $this->model->getUsers();

        $this->db->clear();
        $this->assertTrue($result['code'] ==  200);
    }
/*
    //
    //  @dataProvider providerPostFalse
    //

    public function testPostUsersFalse($params)
    {
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $result = $this->model->postUsers($params);
        $this->db->addToClear("DELETE FROM booker_users WHERE id = '1'");
        $this->db->clear();
        $this->assertTrue($result['code'] == 404);
     }    

    public function testPostUsersTrue()
    {
        $params = array('login' => 'testLogin1',
                        'password' => '111111',
                        'email' => 'test1@gmail.com',
                        'role' => '1');     

        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $this->db->addDBRecord($queryAddRole, $queryDelRole);

        $result = $this->model->postUsers($params);

        $this->db->addToClear("DELETE FROM booker_users WHERE id >= '1'");
        $this->db->clear();
        $this->assertTrue($result['code'] == 200);
    } 
*/
    /**
     * @dataProvider providerPutFalse
     */

    public function testPutUsersFalse($params)
    {
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $this->db->addDBRecord($queryAddRole, $queryDelRole);

        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time)
                         VALUES ('1', 'testLogin', 'testEmail@gmail.com', 'testPassword', '1', 'testHash', '000001')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";
        $this->db->addDBRecord($queryAddUser, $queryDelUser);

        $result = $this->model->putUsers($params);
        $this->assertTrue($result['code'] == 404);
        $this->db->clear();
    } 

    /**
     * @dataProvider providerPutTrue
     */

    public function testPutUsersTrue($params)
    {
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id >= '1'";
        $this->db->addDBRecord($queryAddRole, $queryDelRole);

        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time)
                         VALUES ('1', 'testLogin', 'testEmail@gmail.com', 'testPassword', '1', 'testHash', '000001')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";
        $this->db->addDBRecord($queryAddUser, $queryDelUser);
        $params['id_user'] = '1';

        $result = $this->model->putUsers($params);
        $this->db->clear();
        $this->assertTrue($result['code'] == 200);
    } 

    public function testGetUsersByParamsFalse()
    {
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $this->db->addDBRecord($queryAddRole, $queryDelRole);

        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time)
                         VALUES ('1', 'testLogin', 'testEmail@gmail.com', 'testPassword', '1', 'testHash', '000001')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";
        $this->db->addDBRecord($queryAddUser, $queryDelUser);
        $params = '';

        $result = $this->model->getUsersByParams($params);
        $this->db->clear();
        $this->assertTrue($result['code'] == 404);
    }

    public function testGetUsersByParamsTrue()
    {
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $this->db->addDBRecord($queryAddRole, $queryDelRole);

        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time)
                         VALUES ('1', 'testLogin', 'testEmail@gmail.com', 'testPassword', '1', 'testHash', '000001')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";
        $this->db->addDBRecord($queryAddUser, $queryDelUser);
        $params = '1';

        $result = $this->model->getUsersByParams($params);
        $this->db->clear();
        $this->assertTrue($result['code'] == 200);
    }

    public function providerPostFalse()
    {
        return array(
            array(
                array('login' => '',
                      'password' => '',
                      'email' => '',
                      'role' => '')),
            array(
                array('login' => 'testLogin',
                      'password' => '',
                      'email' => 'test@gmail.com',
                      'role' => '1')),
            array(
                array('login' => 'testLogin',
                      'password' => '',
                      'email' => 'test',
                      'role' => '1'))
        );
    }

    public function providerPutFalse()
    {
        return array(
            array(
                array('id_user' => '1',
                      'user_status' => 'noDisabled')),
            array(
                array('id_user' => '1',
                      'login' => 'testLogin',
                      'email' => 'test')),
            array(
                array('id_user' => 'invalid',
                      'login' => 'testLogin',
                      'email' => 'test@gmail.com'))
        );
    }
    
    public function providerPutTrue()
    {
        return array(
            array(
                array('user_status' => 'disabled')),
            array(
                array('login' => 'testLogin',
                      'email' => 'test@gmail.com'))
        );
    }    
}
