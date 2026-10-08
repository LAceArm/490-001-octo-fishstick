#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($username,$password,$email)
{
   
	echo "LOGIN ATTEMPT".PHP_EOL;
	$webserver=new rabbitMQServer("WebServer.ini","frontEnd");
    // lookup username in database
	// check password
   $dbClient=new rabbitMQClient("SQLMQ.ini","sqlServer");
   $request=array();
   $request['type']="login";
   $request['username']=$username;
   $request['password']=$password;
   $request['message']="Check if record exists";
   $response=$dbClient->send_request($request);
   if($response){
	   if($response["Message"]==="Record found"){ 
		   echo "SUCCESS";
		   $response["returnCode"]='1';	   
            	$webserver->process_message($response);
		   return $response;
	   }
   }
   echo "LOGIN FAILED".PHP_EOL;
   $response["Message"]="Login failed";
   $response["returnCode"]='0';
   $webserver->process_message($response);
    return $response;
    //return false if not valid
}

function doValidate($sessionId)
{
	echo "VALIDATION ATTEMPT".PHP_EOL;
	//Validates sessionID
	$dbClient=new rabbitMQClient("SQLMQ.ini","sqlServer");
	$request=array();
	$request["SessionID"]=$sessionID;
	$request["Message"]="Check if SessionID is valid";
	$response=$dbClient->send_request($request);
	if($response){
		if($response["Message"]==="Record found"){
			echo "SUCCESS";
			$response["returnCode"]='1';
			$webserver->process_message($response);
			return $response;
		}  	
	}
	echo "VALIDATION FAILED".PHP_EOL;
	return array("Message"=>"Validation failed","returnCode"=>'0');
}

//Function tries to register. If it succeeds, it will return true. Otherwise, it'll return false
function doRegister($username,$password,$email)
{ 
	echo "REGISTER ATTEMPT".PHP_EOL;
	//Tries to send data to database. Awaits response from database. If database says 
	//record was inserted successfully, return true
	$dbClient=new rabbitMQClient("SQLMQ.ini","sqlServer"); //Client. Will send request to database.
	$request=array();
	$request['type']="Register";
	$request['username']=$username;
	$request['password']=$password;
	$request["Message"]='Insert into database';
	$response=$dbClient->send_request($request); //Send Request
	if($response){  //Request did not fail
		if ($response["Message"]==="Record found"){
			$response["returnCode"]='1';
			$webserver->process_message($response);
			return $response;
	       	}
	}
	echo "REGISTRATION FAILED".PHP_EOL;
	return array("returnCode"=>'0',"message"=> "Registration failed");
}
function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
	//$webserver->process_message($request);
  switch ($request['type'])
  {
    case "Login":
      return doLogin($request['username'],$request['password'], $request["email"]);
    case "validate_session":
       return doValidate($request['sessionId']);
    case "register":
 	return doRegister($request['username'],$request['password'],$request["email"]);	    
  }
  return array("returnCode" => '0', 'message'=>"Invalid type");
}

//$server = new rabbitMQServer("SQLMQ.ini","sqlServer");

$webserver=new rabbitMQServer("WebServer.ini","frontEnd");

echo "BROKER  BEGIN".PHP_EOL;
//$server->process_requests('requestProcessor');
$webserver->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

