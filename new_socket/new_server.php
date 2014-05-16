<?php
//SIGHT System - Summary Generation
//Chrome Extension v1.0
//By Gabriel Sina - Last update May 16, 2014
/////////////////////////////////////////////
include_once "VEM.php";
include_once "SIGHT.php";

$host = 'localhost'; //host
$port = '8888'; //port
$null = NULL; //null var

//Create TCP/IP sream socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//reuseable port
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

//bind socket to specified host
socket_bind($socket, 0, $port);

//listen to port
socket_listen($socket);

//create & add listning socket to the list
$clients = array($socket);

//start endless loop, so that our script doesn't stop
while (true) {
	//manage multiple connections
	$changed = $clients;
	//returns the socket resources in $changed array
	socket_select($changed, $null, $null, 0, 10);
	
	//check for new socket
	if (in_array($socket, $changed)) {
		$socket_new = socket_accept($socket); //accpet new socket
		$clients[] = $socket_new; //add socket to client array
		
		$header = socket_read($socket_new, 1024); //read data sent by the socket
		perform_handshaking($header, $socket_new, $host, $port); //perform websocket handshake
		
		socket_getpeername($socket_new, $ip); //get ip address of connected socket
		$response = mask(json_encode(array('type'=>'system', 'action'=>'connected'))); //prepare json data
		send_message($response, $socket_new); //notify all users about new connection
		
		//make room for new socket
		$found_socket = array_search($socket, $changed);
		unset($changed[$found_socket]);
	}
	
	//loop through all connected sockets
	foreach ($changed as $changed_socket) {	
		
		//check for any incomming data
		while(socket_recv($changed_socket, $buf, 10240, 0) >= 1)
		{
			//Reads message content
			$received_text = unmask($buf); //unmask data
			$tst_msg = json_decode($received_text); //json decode 
			$user_text = $tst_msg->{"text"}; //message text
			$user_image = $tst_msg->{"image"}; //messaged image
			$user_id = $tst_msg->{"graphid"}; //graph ID

			/* COMMENTED BECAUSE PERL SCRIPT IS DEALING WITH WHETHER THE ARTICLE IS L21 OR L212 ALREADY
			// PLEASE USE THIS SCRIPT IF YOU WILL CALL DIFFERENT APPLICATIONS THAT DOES NOT TREAT THIS ISSUE
			//Provisory way to decide wheter call pearl script or SIGHT
			//print_r("thats it: ".$user_id);
			if($user_id=="L21" || $user_id=="L212"){
				//tries to run the pearl script, going throught VEM
				$text_output = "";
				$vem = new VEM($user_image, $user_text, $user_id); //instantiates a VEM object
				$successful_run = $vem->runVEM(); //boolean
				if($successful_run==true){
					$text_output = $vem->$output; //sets the summary here
				}
				else{
					$text_output = "VEM Way Failed";// runVEM failed
				}
			}
			else{
				//tries to run going throught SIGHT
				$text_output ="";
				$sight = new SIGHT($user_text, $user_id);
				$successful_run = $sight->runSIGHT();
				if($successful_run==true){
					$text_output = $sight->output;
				}
				else{
					$text_output = "SIGHT Way Failed";
				}
			}*/

			//THIS IS THE ALTERNATIVE WAY, WITHOUT CHECKING WHETHER IT IS L21 OR L212
			//BECAUSE THE CURRENT PERL SCRIPT DOES IT ALREADY.
			//tries to run the pearl script, going throught VEM
			$text_output = ""; //summary text output
			$mrp = ""; //most relevant paragraph number

			$vem = new VEM($user_image, $user_text, $user_id); //instantiates a VEM object
			$successful_run = $vem->runVEM(); //boolean
			if($successful_run==true){
				$text_output = $vem->$output; //sets the summary here
				$mrp = $vem->$mrp_output; //sets the most relev. p. number here
			}
			else{
				$text_output = "VEM Way Failed";// runVEM failed
			}


			//prepare data to be sent to client
			$response_text = mask(json_encode(array('type'=>'output', 'output'=>$text_output, 'mrp'=>$mrp)));
			//$response_text = json_encode(array('type'=>'output', 'text'=>$output));
			send_message($response_text, $changed_socket); //send data
			
			break 2; //exist this loop
		}
		
		$buf = @socket_read($changed_socket, 2048, PHP_NORMAL_READ);
		if ($buf === false) { // check disconnected client
			// remove client for $clients array
			$found_socket = array_search($changed_socket, $clients);
			socket_getpeername($changed_socket, $ip);
			unset($clients[$found_socket]);
			
			//notify all users about disconnected connection
			$response = mask(json_encode(array('type'=>'system', 'action'=>'disconnected')));
			send_message($response, $changed_socket);
		}
	}
}
// close the listening socket
socket_close($sock);

//sends (write) message to client
function send_message($msg, $changed_socket)
{
	print_r($msg);
	@socket_write($changed_socket,$msg,strlen($msg));
	return true;
}


//Unmask incoming framed message
function unmask($text) {
	$length = ord($text[1]) & 127;
	if($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	}
	elseif($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	}
	else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
}

//Encode message for transfer to client.
function mask($text)
{
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	
	if($length <= 125)
		$header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536)
		$header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536)
		$header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}

//handshake new client.
function perform_handshaking($receved_header,$client_conn, $host, $port)
{
	$headers = array();
	$lines = preg_split("/\r\n/", $receved_header);
	foreach($lines as $line)
	{
		$line = chop($line);
		if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
		{
			$headers[$matches[1]] = $matches[2];
		}
	}

	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
	//hand shaking header
	$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
	"Upgrade: websocket\r\n" .
	"Connection: Upgrade\r\n" .
	"WebSocket-Origin: $host\r\n" .
	"WebSocket-Location: ws://$host:$port/demo/shout.php\r\n".
	"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
	socket_write($client_conn,$upgrade,strlen($upgrade));
}
