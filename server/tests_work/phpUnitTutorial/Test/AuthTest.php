<?php
include_once('../api/auth/index.php');
include_once('phpUnitTutorial/Test/DbTest.php');

class AuthTest extends PHPUnit_Framework_TestCase
{
    private $model, $db;

    public function setUp()
    {
        $this->model = new Auth();
        $this->db = new DbTest();
    }

    public function tearDown()
    {
        $this->db->clear();
    }

    /**
     * @dataProvider providerPutAuthFalse
     */

    public function testPutAuthFalse($params)
    {
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";

        $params['testPassHash'] = password_hash($params['password'], PASSWORD_BCRYPT);
        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '$params[login]', 'testEmail@gmail.com', '$params[testPassHash]', '1', '1', '1')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";

        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser, $queryDelUser);

        $result = $this->model->putAuth($params);
        $this->db->clear();
        $this->assertTrue($result['code'] == 404); 
    }

    /**
     * @dataProvider providerPutAuthTrue
     */

    public function testPutAuthTrue($params)
    {
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";

        $params['testPassHash'] = password_hash($params['password'], PASSWORD_BCRYPT);
        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '$params[login]', 'testEmail@gmail.com', '$params[testPassHash]', '1', '1', '1')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";

        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser, $queryDelUser);

        $result = $this->model->putAuth($params);
        $this->db->clear();
        $this->assertTrue($result['code'] == 200); 
    }

    public function testGetAuthByParamsTrue()
    {
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";

        $testId = 1;
        $testHash = 'a25a50ef8d0fe9d39036a5ded399b54f';

        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('$testId', 'testLogin', 'testEmail@gmail.com', 'testPassHash', '1', '$testHash', '1')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";

        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser, $queryDelUser);

        $params = $testId . '/' . $testHash;
        $result = $this->model->getAuthByParams($params);
        $this->db->clear();
        $this->assertTrue($result['code'] == 200); 
    }
 
    /**
     * @dataProvider providerGetAuthByParamsFalse
     */

    public function testGetAuthByParamsFalse($data)
    {
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";

        $testId = $data['id'];
        $testHash = $data['hash'];

        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('$testId', 'testLogin', 'testEmail@gmail.com', 'testPassHash', '1', '$testHash', '1')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";

        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser, $queryDelUser);

        $params = $testId . '/' . $testHash;
        $result = $this->model->getAuthByParams($params);
        $this->db->clear();
        $this->assertTrue($result['code'] == 404); 
    }

    public function providerPutAuthFalse()
    {
        return array(
            array(
                array('login' => '',
                      'password' => 'testPassword')),
            array(
                array('login' => 'testLogin',
                      'password' => ''))
        );
    }

    public function providerPutAuthTrue()
    {
        return array(
            array(
                array('login' => 'testLogin',
                      'password' => 'testPassword')),
            array(
                array('login' => '111111',
                      'password' => '111111'))
        );
    }

    public function providerGetAuthByParamsFalse()
    {
        return array(
            array(
                array('id' => '1',
                      'hash' => '123a25a50ef8d0fe9d39036a5ded399b54f')), //more than 32 
            array(
                array('id' => '1',
                      'hash' => ''))
        );
    }

} 

