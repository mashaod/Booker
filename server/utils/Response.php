<?php
/*
*   Class: Response 
*   
*   Model a Response on the system. Supports sending 
*   header and output data from model across Rest Class.
*/

class Response
{
    static function createResponse($data, $code = 200, $useFormat)
    {
        $codeNum = strval($code);
        if($codeNum[0] == 2)
        {
            if (empty($useFormat) || ($useFormat != '.json' && $useFormat != '.txt' && $useFormat != '.html' && $useFormat != '.xml'))
                $useFormat = FORMAT_RESPONSE;    
                $response = Converter::convertFormat($useFormat, $data);
        }
        else
            $response = 'ERROR:' . $code . " Message: " . $data;
            self::sendStatus($code);
            echo $response;
    }

    static function sendStatus($code){
        switch($code)
        {
        case '200':
            header("HTTP/1.0 200 OK");            
            break;
        case '201':
            header("HTTP/1.0 201 Created");            
            break;
        case '202':
            header("HTTP/1.0 202 Accepted");            
            break;
        case '204':
            header("HTTP/1.0 204 No Content");            
            break;   
        case '401':
            header("HTTP/1.0 401 Unauthorized");            
            break;
        case '404':
            header("HTTP/1.0 404 Not Found");
            break;
        case '406':
            header("HTTP/1.0 406 Not Acceptable");
            break;
        case '500':
            header("HTTP/1.0 500 Internal Server Error");
            break;
        case '505':
            header("HTTP/1.0 505 HTTP Version Not Supported");
            break;
        default:
            break; 
        }  
    }
}
