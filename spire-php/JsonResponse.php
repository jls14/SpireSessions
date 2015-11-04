<?php
//JsonResponse.php
class JsonResponse
{
	private static $http_response_code = array(
        200 => 'OK',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found'
    );

    public static function getStatusMsg(/*int*/$status)
    {
        if(isset(self::$http_response_code[$status])){
            return self::$http_response_code[$status];
        }
        return "$status - Unknown Status";
    }

    public static function sendResponse(/*int*/$status, /*data to send*/$data)
    {
    	 header('HTTP/1.1 '.$status.' '.self::getStatusMsg($status));
    	 header('Content-Type: application/json; charset=utf-8');
         header('Access-Control-Allow-Origin: *');
         header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, token");
         header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    	 echo json_encode($data);
    }

    public static function corsResponse()
    {
         header('HTTP/1.1 204 No Content');
         header('Content-Type: application/json; charset=utf-8');
         header('Access-Control-Allow-Origin: *');
         header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, token");
         header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    }
}
?>