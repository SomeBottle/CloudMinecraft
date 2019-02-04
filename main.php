<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
set_time_limit(0);
date_default_timezone_set("Asia/Shanghai");
header('Content-type:text/json;charset=utf-8');
$key = $_POST['key'];
$cm = $_GET['action'];
$command = $_POST['command'];
require './conf.php';
require './qcloud.php';
$verify = false;
$result = array();
foreach ($keys as $v) {
    if ($v == $key) {
        $verify = true;
        break;
    }
}
if ($verify) {
    if ($cm == 'create') {
        if (!file_exists('./create.lock')) {
			$prices='';
			$pricea='';
			$chooseins='';
			foreach($prepareins as $v){
				$prices = askpriceins('InquiryPriceRunInstances',$imageid, '',$v);
                $pricea = json_decode($prices, true);
				if(!isset($pricea['Response']['Error'])){
					$chooseins=$v;
					break;
				}
			}
			$price = $pricea['Response']['Price']['InstancePrice']['UnitPrice'];
			$sv['instype']=$chooseins;
			$sv['internalip']='';
            if (floatval($price) <= 0.20) {
				file_put_contents('./server.json',json_encode($sv,true));
                exec('php startServer.php' . ' > /dev/null &'); /*请打开exec支持*/
                file_put_contents('./create.lock', 'Please Wait.');
                updatestatu('Creating.');
                $result['statu'] = 'success';
                $result['msg'] = 'Create Start.';
            } else {
                $result['statu'] = 'error';
                $result['msg'] = 'Server is too expensive.';
                updatestatu('Server Too Expensive.');
            }
        } else {
            $result['statu'] = 'error';
            $result['msg'] = 'Creating.Please Check the Progress.';
        }
    } else if ($cm == 'progress') {
        if (file_exists('./create.progress')) {
            $v = file_get_contents('./create.progress');
            $result['msg'] = $v;
        } else {
            $result['statu'] = 'error';
            $result['msg'] = 'Not Running.';
        }
    } else if ($cm == 'skip') {
        if (file_exists('./create.progress')) {
            file_put_contents('./stop.txt','');
            $result['msg'] = 'Stopped one Step';
        } else {
            $result['statu'] = 'error';
            $result['msg'] = 'Not Running.';
        }
    } else if ($cm == 'skip') {
        if (file_exists('./create.progress')) {
            file_put_contents('./stop.txt','');
            $result['msg'] = 'Stopped one Step';
        } else {
            $result['statu'] = 'error';
            $result['msg'] = 'Not Running.';
        }
    } else if ($cm == 'sendcommand') {
        if (file_exists('./create.progress')) {
			file_put_contents('./command.request',$command);
            exec('php runCommand.php' . ' > /dev/null &'); /*请打开exec支持*/
            $result['msg'] = 'Sent to the Server.';
        } else {
            $result['statu'] = 'error';
            $result['msg'] = 'Not Running.';
        }
    }
} else {
    $result['statu'] = 'error';
    $result['msg'] = 'Access Denied.';
}
echo json_encode($result, true);
?>