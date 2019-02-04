<?php
/*Server create & Initialize*/
set_time_limit(0);
ignore_user_abort(); 
require './qcloud.php';
if(!cli()){
	echo 'Hello Cloud.';
	exit();
}
date_default_timezone_set("Asia/Shanghai");
$serverc=json_decode(file_get_contents('./server.json'),true);
$serveri=$serverc['instype'];
$gpass=grc(14);
$crtime=time();/*创建时间*/
$remaintime=$waitfordestroy;/*保留40分钟*/
$cr=createins('RunInstances',$imageid,$gpass,$serveri,$remaintime);
$dcr=json_decode($cr,true);
$insid=$dcr['Response']['InstanceIdSet'][0];
$ip='';
$pubip='';
updatestatu('Waiting for Internal IP.');/*Update Progress.*/
while(true&&!file_exists('./stop.txt')){
	sleep(5);
	$dr=requestins('DescribeInstances',$insid);
	$p=json_decode($dr,true);
	$cip=$p['Response']['InstanceSet'][0]['PrivateIpAddresses'][0];
	$pip=$p['Response']['InstanceSet'][0]['PublicIpAddresses'][0];
	if(!empty($cip)){
		$ip=$cip;
		$pubip=$pip;
		break;
	}
}
if(file_exists('./stop.txt')){
	updatestatu('Failed:Exit Progress.');/*Update Progress.*/
	unlink('./stop.txt');
	unlink('./create.lock');
    unlink('./server.json');
	delins('TerminateInstances',$insid,'');/*释放*/
	exit();
}
$serverc['publicip']=$pubip;
$serverc['internalip']=$ip;
$serverc['insid']=$insid;
$serverc['passwd']=$gpass;
$serverc['createtime']=$crtime;
$serverc['remaintime']=$remaintime;
file_put_contents('./server.json',json_encode($serverc,true));
updatestatu('Get Inside IP:'.$ip);/*Update Progress.*/
setsafeins('AssociateSecurityGroups',$insid,$secugroupid,'');/*Security Group*/
updatestatu('Waiting for Initialization.');/*Update Progress.*/
sleep(60);/*wait for initialization*/
updatestatu('Uploading Files.');/*Update Progress.*/
require './sftpconnect.php';
$localpath='/www/wwwroot/cloudmc.imbottle.com/server.zip';
$serverpath='/root/mc/server.zip';
connectssh($config);
shell('mkdir mc','');
shell('chmod -R 777 mc','');
$st = $ftp->upftp($localpath,$serverpath); 
if($st==true){/*Success*/
	updatestatu('Connecting SSH.');/*Update Progress.*/
	while(true){
		if($connectstatu){
			break;
		}
	}
	updatestatu('Deploying Minecraft Server.');/*Update Progress.*/
	shell('mkdir mc','');
	shell('mv server.zip /root/mc/server.zip','');
	shell('yum install -y screen','');
	sleep(20);
	shell('screen -dmS minecraft','');
	shell('screen -x -S minecraft -p 0 -X stuff "cd mc"','block');
	shell('screen -x -S minecraft -p 0 -X stuff "\n"','block');
	shell('screen -x -S minecraft -p 0 -X stuff "unzip server.zip"','block');
	shell('screen -x -S minecraft -p 0 -X stuff "\n"','block');
	shell('screen -x -S minecraft -p 0 -X stuff "chmod -R 777 jdk"','block');
	shell('screen -x -S minecraft -p 0 -X stuff "\n"','block');
	sleep(10);
	shell('screen -x -S minecraft -p 0 -X stuff "/root/mc/jdk/bin/java -Xmx512M -Xms32M -jar /root/mc/paper.jar"','block');
	shell('screen -x -S minecraft -p 0 -X stuff "\n"','block');
	shell('rm -rf /root/mc/server.zip','');
	while(true){
		$javast=shell('lsof -i:25565','block');/*等到服务器完全开启再进入下一步*/
		if(!empty($javast)||file_exists('./stop.txt')){
			if(file_exists('./stop.txt')){
				unlink('./stop.txt');
			}
			break;
		}
		sleep(3);
	}
	ssh2_disconnect($connection);/*SSH2 Close SSH Connection*/
	updatestatu('Successfully Deployed! Server IP: '.$pubip);/*Update Progress.*/
	exec('php checkServer.php'. ' > /dev/null &');/*(非阻塞)请打开exec支持*/
}else{
	unlink('./create.lock');
	updatestatu('Failed:File Upload Error.');/*Update Progress.*/
	unlink('./server.json');
	delins('TerminateInstances',$insid,'');/*释放*/
}
?>