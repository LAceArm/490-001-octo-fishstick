#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($username,$password)
{
    // lookup username in databas
    // check password
    return true;
    //return false if not valid
}

function doValidate($sessionId)
{
	//Validates sessionID
	return true;
}

//Function tries to register. If it succeeds, it will return true. Otherwise, it'll return false
function doRegister($username,$password)
{ 
	//Tries to send data to database. Awaits response from database. If database says 
	//record was inserted successfully, return true
	return true;
}
function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
       return doValidate($request['sessionId']);
    case "register":
 	return doRegister($request['username'],$request['password']);	    
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("SQLMQ.ini","sqlServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

