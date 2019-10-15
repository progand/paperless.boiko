<?php
$LOGIN = 'xxx';
$PASSWORD = 'xxx';
$SESSION_ID = 'xxx';
/*
    Prerequirities:
    	https://stackoverflow.com/questions/6382539/call-to-undefined-function-curl-init
*/

/*
 Send json via POST request
 `sudo apt-get install php-curl
 */
function post_json($url, $data){
	// 	$data = array(
	//     'username' => 'tecadmin',
	//     'password' => '012345678'
	//  );
	 
	$payload = json_encode($data);
	 
	// Prepare new cURL resource
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	 
	// Set HTTP Header for POST request 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    'Content-Type: application/json',
	    'Content-Length: ' . strlen($payload))
	);
	 
	// Submit the POST request
	$result = json_decode(curl_exec($ch));
	if (!$result) {
	  $info = curl_getinfo($ch);
	  var_dump($info);
	  return false;
	}
	// Close cURL session handle
	curl_close($ch);

	return $result;
}

function get_json($url){
	global $SESSION_ID;
	// создание нового cURL ресурса
	$ch = curl_init($url);

	// установка URL и других необходимых параметров
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_COOKIE, 'sessionId=' . $SESSION_ID);
	// curl_setopt($ch, CURLOPT_VERBOSE, true);

	// загрузка страницы и выдача её браузеру
	$result = json_decode(curl_exec($ch));
	if (!$result) {
	  $info = curl_getinfo($ch);
	  var_dump($info);
	  return false;
	}
	// завершение сеанса и освобождение ресурсов
	curl_close($ch);

	return $result;
}

function post_file($url){
	// https://ryansechrest.com/2012/07/send-and-receive-binary-files-using-php-and-curl/
	global $SESSION_ID;
	$header = array('Content-Type: multipart/form-data');
	$fields = array('filename' => '@' . realpath('exampledoc.pdf'));
	$sessionId = $SESSION_ID;
	 
	$resource = curl_init();
	curl_setopt($resource, CURLOPT_URL, $url);
	curl_setopt($resource, CURLOPT_HTTPHEADER, $header);
	curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($resource, CURLOPT_POST, 1);
	curl_setopt($resource, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($resource, CURLOPT_COOKIE, 'sessionId=' . $sessionId);
	// curl_setopt($resource, CURLOPT_VERBOSE, true);
	$result = json_decode(curl_exec($resource));
	if (!$result) {
	  $info = curl_getinfo($resource);
	  var_dump($info);
	  return false;
	}
	curl_close($resource);

	return $result;
}

/*
	Sign in with login and password
*/
function sign_in($login, $password){
	$result = post_json('https://paperless.com.ua/api/login', array(
	    'login' => $login,
	    'password' => $password, 
	    'otp' => 0
	));
	echo "Success\n";

	var_dump($result);
	if($result->sessionId){
		$SESSION_ID = $result->sessionId;
	}
}

/*
	Check whether sessionId is valid
*/
function check_session(){
	$user = get_json('https://paperless.com.ua/api/login');
	// var_dump($user);
	if($user->sessionId){
		echo 'Session id OK\n';
		return true;
	} else {
		echo 'Session id expired!\n';
		return false;
	}
}

/*
	Upload pdf document to paperless, returns info about document.
*/
function upload_file(){
	return post_file('https://paperless.com.ua/upload');
}


// sign_in($LOGIN, $PASSWORD);
// check_session();
upload_file();
// echo "hello\n";

function main(){
	global $LOGIN, $PASSWORD;
	echo "Starting...\n";
	// check session and login if sessionId expired
	if(!check_session()){
		echo "Signing in with login and password...\n";
		sign_in($LOGIN, $PASSWORD);
	}
	// upload PDF file
	echo "Starting file upload...\n";
	$doc = upload_file();
	if (!$doc) {
		echo "File upload failed!!!\n";
		return;
	}

	echo "Finished...\n";
}

main();
 
?>
