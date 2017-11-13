<?php
/*
*   Class: Rest 
*   
*   Supports server input data, transferring them to models and calling 
*   Response Class.
*/

include_once('/usr/home/user1/public_html/Booker/server/utils/config.php');
include_once('/usr/home/user1/public_html/Booker/server/utils/Db.php');
include_once('/usr/home/user1/public_html/Booker/server/utils/helpers/Validator.php');
include_once('/usr/home/user1/public_html/Booker/server/utils/helpers/Converter.php');
include_once('/usr/home/user1/public_html/Booker/server/utils/Response.php');

// include_once(__DIR__ . '/config.php');
// include_once(__DIR__ . '/Db.php');
// include_once(__DIR__ . '/helpers/Validator.php');
// include_once(__DIR__ . '/helpers/Converter.php');
// include_once(__DIR__ . '/Response.php');

class Rest
{
    protected $params;
    protected $table;
    protected $method;
    protected $contentFormat;
	
    public function start()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: PUT, POST, GET, DELETE');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        $this->parsUrl();

        switch($this->method)
        {
        case 'GET':
            if($this->params == '')
                $this->setMethod('get'.ucfirst($this->table));
            else
                $this->setMethod('get'.ucfirst($this->table).'ByParams');
            break;
        case 'DELETE':
            $this->setMethod('delete'.ucfirst($this->table));
            break;
        case 'POST':
            $this->params = $_POST;
            $this->setMethod('post'.ucfirst($this->table));
            break;
        case 'PUT':
            $id= explode('/', $this->params, 1);
            $this->params = Converter::convertPut(file_get_contents("php://input")."&id=".$id);
            $this->setMethod('put'.ucfirst($this->table));
            break;
        default:
            return false;
        }
    }

    protected function setMethod($meth)
    {
        if (method_exists($this, $meth))
        {
            $response = $this->$meth($this->params);
            Response::createResponse($response['data'], $response['code'], $this->contentFormat);
        }
        else
            Response::createResponse(ERR_001, 505, $this->contentFormat);
    }

    protected function getResponse($data, $code = 200)
    {
        return array('data'=>$data,'code'=>$code);
    }

    protected function parsUrl()
    {   
        $url = $_SERVER['REQUEST_URI'];
        list($s, $a, $d, $f, $db, $table, $path) = explode('/', $url, 7);
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->table = $table;
        if($path != '')
        {
            $clearString = mb_strtolower(strip_tags($path));
            $data = trim($clearString);
            preg_match("/\.\w+$/", $data, $format);
            $this->contentFormat = $format[0];
            $params = preg_replace("/\.\w+$/", "", $data);
            $this->params = Validator::clearData($params);
        }
    }
}
