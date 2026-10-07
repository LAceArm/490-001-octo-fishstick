#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($username,$password)
{
    // lookup username in database
	// check password
   $dbClient=new rabbitMQClient("SQLMQ.ini","sqlServer");
   $request=array();
   $request['type']="Login";
   $request['username']=$username;
   $request['password']=$password;
   $request['message']="Check if record exists";
   $response=$dbClient->send_request($request);
   if($response){
	   if($response["Message"]==="Record found"){ 
		   return true;
	   }
   }
    return false;
    //return false if not valid
}

function doValidate($sessionId)
{
	//Validates sessionID
	$dbClient=new rabbitMQClient("SQLMQ.ini","sqlServer");
	$request=array();
	$request["SessionID"]=$sessionID;
	$request["Message"]="Check if SessionID is valid";
	$response=$dbClient->send_request($request);
	if($response){
	     if($response["Message"]==="Record found"){return true;}  	
	}
	return false;
}

//Function tries to register. If it succeeds, it will return true. Otherwise, it'll return false
function doRegister($username,$password)
{ 
	//Tries to send data to database. Awaits response from database. If database says 
	//record was inserted successfully, return true
	$dbClient=new rabbitMQClient("SQLMQ.ini","sqlServer");
	$request=array();
	$request['type']="Register";
	$request['username']=$username;
	$request['password']=$password;
	$request["Message"]='Insert into database';
	$response=$dbClient->send_request($request);
	if($response){ 
		if ($response["Message"]==="Record found"){
			return true;
	       	}
	}
	return false;
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

$webserver=new rabbitMQServer("WebServer.ini","frontEnd");

echo "BROKER  BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
$webserver->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

