<?php
/*利用ssh2扩展*/
if(!cli()){
	echo 'Hello Cloud.';
	exit();
}
require './sftp.class.php';
$config = array(
    'host' => $ip,
    //服务器
    'port' => '22',
    //端口
    'username' => 'root',
    //用户名
    'password' => $gpass
);
$ftp = new Sftp($config);
$connection='';
$connectstatu=false;
function connectssh($c){
	global $connection;
	global $connectstatu;
	$host=$c['host'];
	$port=$c['port'];
	$user=$c['username'];
	$passwd=$c['password'];
    $GLOBALS['connection'] = ssh2_connect($host, $port);
    if (!$connection) {
        unlink('./create.lock');
		updatestatu('Failed:Unable to connect to Server.');/*Update Progress.*/
    }
    $auth_methods = ssh2_auth_none($connection, $user);
    if (in_array('password', $auth_methods)) {
        /*通过password方式登录远程服务器*/
        if (ssh2_auth_password($connection, $user, $passwd)) {
			$GLOBALS['connectstatu']=true;
        } else {
            unlink('./create.lock');
			updatestatu('Failed:Unable to connect to Server.');/*Update Progress.*/
        }
    }
}
function shell($t,$m){
	global $connection;
	$stream=ssh2_exec($connection, $t);
	if($m=='block'){
	   stream_set_blocking($stream,1);
	}else{
	   stream_set_blocking($stream,0);
	}
    return (stream_get_contents($stream));
}