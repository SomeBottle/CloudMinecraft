<?php
/*服务器销毁部分*/
set_time_limit(0);
ignore_user_abort(); 
require './qcloud.php';
if(!cli()){
	echo 'Hello Cloud.';
	exit();
}
$waitforzip=$ziptime;
date_default_timezone_set("Asia/Shanghai");
$server=json_decode(file_get_contents('./server.json'),true);
$pubip=$server['publicip'];
$ip=$server['internalip'];
$gpass=$server['passwd'];
$ins=$server['insid'];
require './sftpconnect.php';
connectssh($config);
shell('screen -x -S minecraft -p 0 -X stuff "stop"','block');
shell('screen -x -S minecraft -p 0 -X stuff "\n"','');/*相当于回车*/
sleep(5);
while(true){
    $javast=shell('lsof -i:25565','block');/*等到服务器完全停止再进入下一步*/
	if(empty($javast)||file_exists('./stop.txt')){
		if(file_exists('./stop.txt')){
			unlink('./stop.txt');
		}
		break;
	}
	sleep(3);
}
shell('screen -x -S minecraft -p 0 -X stuff "\n"','');
shell('screen -x -S minecraft -p 0 -X stuff "zip -r server.zip ./*"','block');
shell('screen -x -S minecraft -p 0 -X stuff "\n"','');/*相当于回车*/
while(true){
	sleep($waitforzip);
    $zipst=shell('ls /root/mc/server.zip','block');/*等到压缩包准备完毕再进入下一步*/
	if(stripos($zipst,'cannot')==false||file_exists('./stop.txt')){
		if(file_exists('./stop.txt')){
			unlink('./stop.txt');
		}
		break;
	}
}
unlink('./server.zip');
sleep(1);
$localpath='/www/wwwroot/cloudmc.imbottle.com/server.zip';
$serverpath='/root/mc/server.zip';
$st = $ftp->downftp($serverpath,$localpath);/*传回存档*/
sleep(3);
ssh2_disconnect($connection);/*关闭ssh2连接*/
unlink('./create.lock');
unlink('./create.progress');
unlink('./server.json');
if(file_exists('./stop.txt')){
	unlink('./stop.txt');
}
sleep(1);
delins('TerminateInstances',$ins,'');/*释放*/
/*程序结束*/
?>