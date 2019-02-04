<?php
/*服务器巡查器*/
set_time_limit(0);
ignore_user_abort(); 
require './qcloud.php';
if(!cli()){
	echo 'Hello Cloud.';
	exit();
}
date_default_timezone_set("Asia/Shanghai");
$server=json_decode(file_get_contents('./server.json'),true);
$checkstart=time();
$pubip=$server['publicip'];
$ip=$server['internalip'];
$gpass=$server['passwd'];
require './sftpconnect.php';
$crtime=intval($server['createtime']);
$rmtime=intval($server['remaintime']);
$endtime=$crtime+$rmtime*60;
connectssh($config);/*Connect to Server*/
$pstatu=true;
$limittime=$checkstart+$allownobody*60;
$noticetime=$limittime-300;
$exitmsg='';
while(true){
	$remained=$endtime-time();
	$javast=shell('lsof -i:25565','block');
	$status=json_decode(file_get_contents('https://api.mcsrvstat.us/1/'.$pubip),true);
	$playern=intval($status['players']['online']);
	if(file_exists('stop.txt')){
		$exitmsg='Requested for Temination.';
		unlink('./stop.txt');
		break;
	}
	if(empty($javast)){/*Java进程判断*/
		$exitmsg='Server unexpected terminated.';
		break;
	}
	if($playern==0&&time()>=$limittime){/*玩家数量判断*/
		$exitmsg='Server terminated because there\'s no player.';
		break;
	}else if($playern==0&&time()>=$noticetime){
		updatestatu('ServerIP:'.$pubip.'\nWarn:五分钟后如果没有玩家将关闭.\nLeft Time:'.($limittime-time()));/*Update Progress.*/
	}else if($playern>0){
		$limittime=time()+$allownobody*60;
		$noticetime=$limittime-300;
		updatestatu('Server:Running.IP:'.$pubip);/*Update Progress.*/
	}
	if($remained<=480){/*快要被销毁*/
		$exitmsg='Server will soon be terminated.';
		shell('screen -x -S minecraft -p 0 -X stuff "say 服务器将被销毁Server Will Be Destroyed Soon."','block');
		shell('screen -x -S minecraft -p 0 -X stuff "\n"','');
		break;
	}
	sleep(5);
}
/*服务器进入关闭程式*/
ssh2_disconnect($connection);/*关闭ssh2连接*/
updatestatu('Server Terminating:'.$exitmsg);/*Update Progress.*/
exec('php terminateServer.php'. ' > /dev/null &');/*(非阻塞)请打开exec支持*/
?>