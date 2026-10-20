#!/usr/bin/php
<?php
	require_once('path.inc');
	require_once('get_host_info.inc');
	require_once('rabbitMQLib.inc');

	$dbClient=new rabbitMQClient('SQLMQ.ini','sqlServer');

	$response=$dbClient->send_request(array("Message"=>"test","type"=>"googly"));

	echo "Received a response".PHP_EOL;

	var_dump($response);
	function doLogin($username,$password){
		echo "LOGIN ATTEMPT FROM $username".PHP_EOL;  

		global $dbClient;

		$request=array();
		
		$request['type']="login";

		$request['username']=$username;

		$request['password']=$password;

		$request["message"]="Incoming login attempt";

		try{ 
			echo "SENDING REQUEST TO DATABASE".PHP_EOL;

			$response=$dbClient->send_request($request);

			if($response["success"]){
				echo "LOGIN WAS SUCCESSFUL".PHP_EOL;
				return array("Message"=>"Successful login","returnCode"=>'1',"success"=>'1');
			}
			else{ 
				echo "LOGIN WAS UNSUCCESSFUL".PHP_EOL;
				return array("Message"=>"Unsuccessful login","returnCode"=>'0',"success"=>'0');
			}

		}
		catch (Exception $e){ 
			echo "THERE WAS AN ERROR: ";
			var_dump($e); echo "\n";
			return array("Message"=>"There was an error","returnCode"=>'-1');
		}
	}

?>
