#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


function doLogin($username,$password,$email)
{
   global $dbClient;
   echo "LOGIN ATTEMPT".PHP_EOL;
   $request=array("Message"=>"Test","username"=>$username,"password"=>$password,"email"=>$email);
   $response=$dbClient->send_request($request);
   echo "Received Response: ".PHP_EOL;
   print_r($response);
   return $response;
   /*
   $request=array(); //Build Request
   $request['type']="login";
   $request['username']=$username;
   $request['password']=$password;
   $request['email']=$email;
   $request['message']="Login Attempt";
   try{ 
	   echo "SENDING REQUEST".PHP_EOL;
	   $response=$dbClient->send_request($request); //try to connect
	   echo "RETURNED FROM DATABASE".PHP_EOL;
	   if($response["success"]){
		  return array("Message"=>"Login success","isUser"=>true); 
	   }
	   else{
		  return array("Message"=>"Login failed","isUser"=>false); 
	   }

   }
   catch(Exception $e){ 
	   echo "CONNECTION FAILED".PHP_EOL; //Fail
   }   
	*/
}

function doValidate($sessionId)
{
	global $dbClient;
	echo "VALIDATION ATTEMPT".PHP_EOL;
	//Validates sessionID
	$request=array();
	$request["SessionID"]=$sessionID;
	$request["Message"]="Check if SessionID is valid";
	try{
		$response=$dbClient->send_request($request);
		if($response["success"]){
			return array("Message"=> "Validation Success","hasSession"=>true);
		}
		else{
		       return array("Message"=>"Validation Failure","hasSession"=>false);	
		}
	} 
	catch (Exception $e){
		echo "ERROR. COULD NOT CONNECT TO DATABASE".PHP_EOL;
		return array("Message"=> "Can't connect to database");	
	}
}

//Function tries to register. If it succeeds, it will return true. Otherwise, it'll return false
function doRegister($username,$password,$email)
{ 
	global $dbClient; //Communicates with database
	echo "REGISTER ATTEMPT".PHP_EOL;
	$request=array();
	$request['type']="Register";
	$request['username']=$username;
	$request['password']=$password;
	$request['email']=$email;
	$request["Message"]='Insert into database';
	try{
		echo "REGISTRATION ATTEMPT. ATTEMPTING TO CONNECT TO DATABASE".PHP_EOL;
		$response=$dbClient->send_request($request);
		if($response["success"]){
			echo "REGISTRATION SUCCESS".PHP_EOL;
			return array("Message"=>"Successfully registered new user","didRegister"=>true);	
		}
		else{
			echo "REGISTER FAILURE.".PHP_EOL;
			return array("Message"=>"Failed to register new user","didRegister"=>false);	
		}
	}
	catch(Exception $e){
		echo "COULD NOT CONNECT".PHP_EOL;
		return array("Message"=>"Can't connect to database");
	}
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
      return doLogin($request['username'],$request['password'], $request["email"]);
    case "validate_session":
       return doValidate($request['sessionId']);
    case "register":
 	return doRegister($request['username'],$request['password'],$request["email"]);	    
  } 
  return array("returnCode" => '0', 'message'=>"Invalid type");
}


$webserver=new rabbitMQServer("WebServer.ini","frontEnd");
$dbClient=new rabbitMQClient("SQLMQ.ini","sqlServer");
$dbServer=new rabbitMQClient("SQLMQ.ini","sqlServer");

echo "BROKER  BEGIN".PHP_EOL;

$webserver->process_requests('requestProcessor');

$dbServer->process_requests('processDB');

echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

