<?php
include_once('../api/events/index.php');
include_once('phpUnitTutorial/Test/DbTest.php');

class EventsTest extends PHPUnit_Framework_TestCase
{
    private $model, $db;

    public function setUp()
    {
        $this->model = new Events();
        $this->db = new DbTest();
    }

    public function tearDown()
    {
        $this->db->clear();
    }

    public function testGetEventsFalse()
    {
        $result = $this->model->getEvents();
        $this->assertEquals($result, array('data' => array(),'code' => '200'));
    }

    public function testGetUsersTrue()
    {
        $queryAddRoom = "INSERT INTO booker_rooms (id, name) VALUES ('1', 'room1')";
        $queryDelRoom = "DELETE FROM booker_rooms WHERE id = '1'";
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '1', '1', '1', '1', '1', '1')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";
        $queryAddEvent = "INSERT INTO booker_events 
                          (id, user_id, room_id, description, time_start, time_end, parent, time_create)
                          VALUES ('1', '1', '1', 'testDescription', '100', '200', '0', '50')";
        $queryDelEvent = "DELETE FROM booker_events WHERE id = '1'";       

        $this->db->addDBRecord($queryAddRoom, $queryDelRoom);
        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser, $queryDelUser);
        $this->db->addDBRecord($queryAddEvent, $queryDelEvent);

        $result = $this->model->getEvents();

        $this->db->clear();
        $this->assertTrue($result['code'] == 200); 
    }

    /**
     * @dataProvider providerPostFalse
     */

    public function testPostEventsFalse($params)
    {
        $queryAddRoom = "INSERT INTO booker_rooms (id, name) VALUES ('1', 'room1')";
        $queryDelRoom = "DELETE FROM booker_rooms WHERE id = '1'";
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '1', '1', '1', '1', '1', '1')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";
        $queryDelEvent = "DELETE FROM booker_events WHERE id >= '1'";

        $this->db->addDBRecord($queryAddRoom, $queryDelRoom);
        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser, $queryDelUser);
  
        $result = $this->model->postEvents($params);

        $this->db->addToClear($queryDelEvent); 
        $this->db->clear();
        $this->assertTrue($result['code'] == 404); 
    } 

    public function testPostEventsTrue()
    {
        $queryAddRoom = "INSERT INTO booker_rooms (id, name) VALUES ('1', 'room1')";
        $queryDelRoom = "DELETE FROM booker_rooms WHERE id = '1'";
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $queryAddUser = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '1', '1', '1', '1', '1', '1')";
        $queryDelUser = "DELETE FROM booker_users WHERE id = '1'";
        $queryDelEvent = "DELETE FROM booker_events WHERE id >= '1'";

        $this->db->addDBRecord($queryAddRoom, $queryDelRoom);
        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser, $queryDelUser);

        $params = array('id_user' => '1',
                      'id_room' => '1',
                      'description' => 'testDescription',
                      'time_start' => '100',
                      'time_end' => '200',
                      'parent' => '0',
                      'time_create' => '50');
   
        $result = $this->model->postEvents($params);

        $this->db->addToClear($queryDelEvent); 
        $this->db->clear();
        $this->assertTrue($result['code'] == 200); 
    }  

    /**
     * @dataProvider providerPutFalse
     */

    public function testPutEventsFalse($params)
    {
        $queryAddRoom = "INSERT INTO booker_rooms (id, name) VALUES ('1', 'room1')";
        $queryDelRoom = "DELETE FROM booker_rooms WHERE id = '1'";
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $queryAddUser1 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '1', '1', '1', '1', '1', '1')";
        $queryAddUser2 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('2', '2', '2', '2', '2', '2', '2')";
        $queryDelUser = "DELETE FROM booker_users WHERE id >= '1'";
        $queryAddEvent = "INSERT INTO booker_events 
                          (id, user_id, room_id, description, time_start, time_end, parent, time_create)
                          VALUES ('1', '1', '1', 'testDescription', '100', '200', '0', '50')";
        $queryDelEvent = "DELETE FROM booker_events WHERE id = '1'";       

        $this->db->addDBRecord($queryAddRoom, $queryDelRoom);
        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser1, $queryDelUser);
        $this->db->addDBRecord($queryAddUser2, $queryDelUser);
        $this->db->addDBRecord($queryAddEvent, $queryDelEvent);

        $result = $this->model->putEvents($params);

        $this->db->addToClear($queryDelEvent); 
        $this->db->clear();
        $this->assertTrue($result['code'] == 404); 
    }
 
    public function testPutEventsTrue()
    {
        $queryAddRoom = "INSERT INTO booker_rooms (id, name) VALUES ('1', 'room1')";
        $queryDelRoom = "DELETE FROM booker_rooms WHERE id = '1'";
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $queryAddUser1 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '1', '1', '1', '1', '1', '1')";
        $queryAddUser2 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('2', '2', '2', '2', '2', '2', '2')";
        $queryDelUser = "DELETE FROM booker_users WHERE id >= '1'";
        $queryAddEvent = "INSERT INTO booker_events 
                          (id, user_id, room_id, description, time_start, time_end, parent, time_create)
                          VALUES ('1', '1', '1', 'testDescription', '100', '200', '0', '50')";
        $queryDelEvent = "DELETE FROM booker_events WHERE id = '1'";       

        $this->db->addDBRecord($queryAddRoom, $queryDelRoom);
        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser1, $queryDelUser);
        $this->db->addDBRecord($queryAddUser2, $queryDelUser);
        $this->db->addDBRecord($queryAddEvent, $queryDelEvent);

        $params = array('id_event' => '1',
                      'id_user' => '2',
                      'description' => 'testDescription2',
                      'time_start' => '200',
                      'time_end' => '300');

        $result = $this->model->putEvents($params);

        $this->db->addToClear($queryDelEvent); 
        $this->db->clear();
        $this->assertTrue($result['code'] == 200); 
    }

    public function testDeleteEventsFalse()
    {
        $queryAddRoom = "INSERT INTO booker_rooms (id, name) VALUES ('1', 'room1')";
        $queryDelRoom = "DELETE FROM booker_rooms WHERE id = '1'";
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $queryAddUser1 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '1', '1', '1', '1', '1', '1')";
        $queryAddUser2 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('2', '2', '2', '2', '2', '2', '2')";
        $queryDelUser = "DELETE FROM booker_users WHERE id >= '1'";
        $queryAddEvent = "INSERT INTO booker_events 
                          (id, user_id, room_id, description, time_start, time_end, parent, time_create)
                          VALUES ('1', '1', '1', 'testDescription', '100', '200', '0', '50')";
        $queryDelEvent = "DELETE FROM booker_events WHERE id = '1'";       

        $this->db->addDBRecord($queryAddRoom, $queryDelRoom);
        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser1, $queryDelUser);
        $this->db->addDBRecord($queryAddUser2, $queryDelUser);
        $this->db->addDBRecord($queryAddEvent, $queryDelEvent);

        $params = '';

        $result = $this->model->deleteEvents($params);

        $this->db->addToClear($queryDelEvent); 
        $this->db->clear();
        $this->assertTrue($result['code'] == 404); 
    }

    public function testDeleteEventsTrue()
    {
        $queryAddRoom = "INSERT INTO booker_rooms (id, name) VALUES ('1', 'room1')";
        $queryDelRoom = "DELETE FROM booker_rooms WHERE id = '1'";
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $queryAddUser1 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '1', '1', '1', '1', '1', '1')";
        $queryAddUser2 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('2', '2', '2', '2', '2', '2', '2')";
        $queryDelUser = "DELETE FROM booker_users WHERE id >= '1'";
        $queryAddEvent = "INSERT INTO booker_events 
                          (id, user_id, room_id, description, time_start, time_end, parent, time_create)
                          VALUES ('1', '1', '1', 'testDescription', '100', '200', '0', '50')";
        $queryDelEvent = "DELETE FROM booker_events WHERE id = '1'";       

        $this->db->addDBRecord($queryAddRoom, $queryDelRoom);
        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser1, $queryDelUser);
        $this->db->addDBRecord($queryAddUser2, $queryDelUser);
        $this->db->addDBRecord($queryAddEvent, $queryDelEvent);

        $params = '1';

        $result = $this->model->deleteEvents($params);

        $this->db->addToClear($queryDelEvent); 
        $this->db->clear();
        $this->assertTrue($result['code'] == 200); 
    }

    public function testGetEventsByParamsFalse()
    {
        $queryAddRoom = "INSERT INTO booker_rooms (id, name) VALUES ('1', 'room1')";
        $queryDelRoom = "DELETE FROM booker_rooms WHERE id = '1'";
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $queryAddUser1 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '1', '1', '1', '1', '1', '1')";
        $queryAddUser2 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('2', '2', '2', '2', '2', '2', '2')";
        $queryDelUser = "DELETE FROM booker_users WHERE id >= '1'";
        $queryAddEvent = "INSERT INTO booker_events 
                          (id, user_id, room_id, description, time_start, time_end, parent, time_create)
                          VALUES ('1', '1', '1', 'testDescription', '100', '200', '0', '50')";
        $queryDelEvent = "DELETE FROM booker_events WHERE id = '1'";       

        $this->db->addDBRecord($queryAddRoom, $queryDelRoom);
        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser1, $queryDelUser);
        $this->db->addDBRecord($queryAddUser2, $queryDelUser);
        $this->db->addDBRecord($queryAddEvent, $queryDelEvent);

        $testIdEvent = 'invalidId';
        $testIdUser = '';
        $params = $testIdEvent . '/' . $testIdUser;

        $result = $this->model->getEventsByParams($params);

        $this->db->addToClear($queryDelEvent); 
        $this->db->clear();
        $this->assertTrue($result['code'] == 404); 
    }

    public function testGetEventsByParamsTrue()
    {
        $queryAddRoom = "INSERT INTO booker_rooms (id, name) VALUES ('1', 'room1')";
        $queryDelRoom = "DELETE FROM booker_rooms WHERE id = '1'";
        $queryAddRole = "INSERT INTO booker_roles (id, name) VALUES ('1', '1')";
        $queryDelRole = "DELETE FROM booker_roles WHERE id = '1'";
        $queryAddUser1 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('1', '1', '1', '1', '1', '1', '1')";
        $queryAddUser2 = "INSERT INTO booker_users (id, login, email, password, role, hash, time) VALUES ('2', '2', '2', '2', '2', '2', '2')";
        $queryDelUser = "DELETE FROM booker_users WHERE id >= '1'";
        $queryAddEvent = "INSERT INTO booker_events 
                          (id, user_id, room_id, description, time_start, time_end, parent, time_create)
                          VALUES ('1', '1', '1', 'testDescription', '100', '200', '0', '50')";
        $queryDelEvent = "DELETE FROM booker_events WHERE id = '1'";       

        $this->db->addDBRecord($queryAddRoom, $queryDelRoom);
        $this->db->addDBRecord($queryAddRole, $queryDelRole);
        $this->db->addDBRecord($queryAddUser1, $queryDelUser);
        $this->db->addDBRecord($queryAddUser2, $queryDelUser);
        $this->db->addDBRecord($queryAddEvent, $queryDelEvent);

        $testIdEvent = '1';
        $testIdUser = '1';
        $params = $testIdEvent . '/' . $testIdUser;

        $result = $this->model->getEventsByParams($params);

        $this->db->addToClear($queryDelEvent); 
        $this->db->clear();
        $this->assertTrue($result['code'] == 200); 
    }

    public function providerPostFalse()
    {
        return array(
            array(
                array('id_user' => '',
                      'id_room' => '1',
                      'description' => 'testDescription',
                      'time_start' => '100',
                      'time_end' => '200',
                      'parent' => '0',
                      'time_create' => '50')),
            array(
                array('id_user' => '1',
                      'id_room' => '',
                      'description' => 'testDescription',
                      'time_start' => '100',
                      'time_end' => '200',
                      'parent' => '0',
                      'time_create' => '50')),
            array(
                array('id_user' => '1',
                      'id_room' => '1',
                      'description' => 'testDescription',
                      'time_start' => '300', //start time before end
                      'time_end' => '200',
                      'parent' => '0',
                      'time_create' => '50')),
        );
    }

    public function providerPutFalse()
    {
        return array(
            array(
                array('id_event' => '',
                      'id_user' => '2',
                      'description' => 'testDescription2',
                      'time_start' => '200',
                      'time_end' => '300')),
            array(
                array('id_event' => '1',
                      'id_user' => '',
                      'description' => 'testDescription2',
                      'time_start' => '200',
                      'time_end' => '300')),
        );
    } 
 
}
 
