<?php
set_time_limit(0);
ignore_user_abort(); 
require './qcloud.php';
if(!cli()){
	echo 'Hello Cloud.';
	exit();
}
@$command=file_get_contents('./command.request');
$server=json_decode(file_get_contents('./server.json'),true);
$ip=$server['internalip'];
$gpass=$server['passwd'];
if(!empty($ip)&&!empty($command)){
	require './sftpconnect.php';
	connectssh($config);/*Connect to Server*/
	shell('screen -x -S minecraft -p 0 -X stuff "\n"','');
	shell('screen -x -S minecraft -p 0 -X stuff "'.$command.'"','block');
	shell('screen -x -S minecraft -p 0 -X stuff "\n"','');
	file_put_contents('./command.request','');
	ssh2_disconnect($connection);/*关闭ssh2连接*/
}
?>