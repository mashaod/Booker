<?php
include_once('../api/rooms/index.php');
include_once('phpUnitTutorial/Test/DbTest.php');

class RoomsTest extends PHPUnit_Framework_TestCase
{
    private $model, $db;
    
	public function setUp()
	{
        $this->model = new Rooms();
        $this->db = new DbTest();
    }
    
	public function tearDown()
	{
		$this->db->clear();
    }

    public function testGetRoomsFalse()
    {
        $this->assertEquals($this->model->getRooms(), array('data'=>array(),'code'=>'200'));
    }
    
    public function testGetRoomsTrue()
    {
        $this->db->addDBRecord("INSERT INTO booker_rooms (id, name) VALUES ('1', 'room1')", "DELETE FROM booker_rooms WHERE id = '1'");
        $result = $this->model->getRooms();
        $this->db->clear();
        $this->assertTrue($result['code'] == 200);
    }    
}
