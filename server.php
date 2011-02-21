#!/php -q
<?php  /*  >php -q server.php  */

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();


function randomColor(){
    mt_srand((double)microtime()*1000000);
    $c = '';
    while(strlen($c)<6){
        $c .= sprintf("%02X", mt_rand(0, 255));
    }
    return '#'.$c;
}


/**
 * callback function 
 * @param WebSocketUser $user Current user
 * @param string $msg Data from user sent
 * @param WebSocketServer $server Server object
 */
function process($user, $msg, $server){
    $data = json_decode($msg);
	$data = objectToArray($data);
    // every websocket user can have mixed data (like position or color)
	if (! isset($user->data['color'])) {
		$user->data['color'] = randomColor();
		$user->data['ip'] = $user->ip;
	}
	$return = array();
	if(isset($data['mouse'])){
		$user->data['posX'] = $data['mouse']['posX'];
		$user->data['posY'] = $data['mouse']['posY'];
		foreach($server->getUsers() as $user){
			$return[$user->id] = $user->data;
		}
	}else if(isset($data['message'])){
		$return[$user->id]['message'] = $data['message'];
		$return[$user->id]['IP'] = $user->data['ip'];
		$return[$user->id]['color'] = $user->data['color'];
	}else{
		return;
	}
	// send the data to all current users
	foreach($server->getUsers() as $user){
		$server->send($user->socket, json_encode($return));
	}
}

require_once 'WebSocketServer.php';
// new WebSocketServer( socket address, socket port, callback function )
$webSocket = new WebSocketServer("localhost", 8080, 'process');
$webSocket->run();

function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}