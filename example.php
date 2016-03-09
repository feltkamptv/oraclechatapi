<?php

include_once('oraclesoapapi.php');

$soapUrl = 'https://your_service_cloud.rightnowdemo.com/cgi-bin/your_service_cloud.cfg/services/chat_soap';
$username = 'YOUR_USERNAME';
$password = 'YOUR_PASSWORD';
$app_id = 'YOUR_APP_ID';
$interface_name = 'your_service_cloud';
$interface_id = 1;

$client = new oracleSoapApi($soapUrl, $username, $password, $app_id, false, $interface_id, $interface_name);

if($client->checkAvailability()){
  $chat_id = $client->requestChat('+3112345678', array('CustomerInformation'=> array('FirstName'=>'Test Script')));
	if(!$chat_id){
    echo $client->error;
	}else{
		$client->setSessionID($chat_id);
		$client->sendMsg('This is a test message.');
		echo 'Test message send.';
	}
}else{
	echo 'Chat not available';
}
