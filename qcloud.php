<?php
require_once './conf.php';
$scid = $secrets['id'];
$sckey = $secrets['key'];
$rand = rand(10000, 999999);
function geturl($ins, $act,$imageid,$pass,$instype,$time)
{
	global $selectregion;
	global $selectzone;
	global $disksize;
    global $scid;
    global $rand;
    $sign = array();
    if ($act == 'DescribeInstances') {
        $sign['Action'] = $act;
        $sign['InstanceIds.0'] = $ins;
        $sign['Limit'] = 20;
        $sign['Nonce'] = $rand;
        $sign['Offset'] = 0;
        $sign['Region'] = $selectregion;
        $sign['SecretId'] = $scid;
        $sign['Timestamp'] = time();
        $sign['Version'] = '2017-03-12';
    } else if ($act == 'InquiryPriceRunInstances') {
            $sign['Action'] = $act;
			$sign['Placement.Zone']=$selectzone;
			$sign['Nonce'] = $rand;
            $sign['ImageId'] = $imageid;
            $sign['SystemDisk.DiskSize'] = $disksize;
			$sign['SystemDisk.DiskType'] = 'CLOUD_BASIC';
			$sign['InternetAccessible.InternetChargeType']='TRAFFIC_POSTPAID_BY_HOUR';
			$sign['InternetAccessible.InternetMaxBandwidthOut']=5;
			$sign['InternetAccessible.PublicIpAssigned']='TRUE';
			$sign['InstanceName']='CloudMinecraft';
			$sign['LoginSettings.Password']=$pass;
            $sign['InstanceChargeType'] = 'POSTPAID_BY_HOUR';
            $sign['InstanceType'] = $instype;
            $sign['Region'] = $selectregion;
            $sign['SecretId'] = $scid;
            $sign['Timestamp'] = time();
            $sign['Version'] = '2017-03-12';
    } else if ($act == 'RunInstances') {
            $sign['Action'] = $act;
			$sign['Placement.Zone']=$selectzone;
			$sign['Nonce'] = $rand;
            $sign['ImageId'] = $imageid;
            $sign['SystemDisk.DiskSize'] = $disksize;
			if(!empty($time)){
				$sign['ActionTimer.Externals.ReleaseAddress'] = 'TRUE';
				$sign['ActionTimer.TimerAction'] = 'TerminateInstances';
				$sign['ActionTimer.ActionTime'] = date('Y-m-d H:i:s',(time()+intval($time)*60));
			}
			$sign['SystemDisk.DiskType'] = 'CLOUD_BASIC';
			$sign['InternetAccessible.InternetChargeType']='TRAFFIC_POSTPAID_BY_HOUR';
			$sign['InternetAccessible.InternetMaxBandwidthOut']=5;
			$sign['InternetAccessible.PublicIpAssigned']='TRUE';
			$sign['InstanceName']='CloudMinecraft';
			$sign['LoginSettings.Password']=$pass;
            $sign['InstanceChargeType'] = 'POSTPAID_BY_HOUR';
            $sign['InstanceType'] = $instype;
            $sign['Region'] = $selectregion;
            $sign['SecretId'] = $scid;
            $sign['Timestamp'] = time();
            $sign['Version'] = '2017-03-12';
    } else if ($act == 'TerminateInstances') {
            $sign['Action'] = $act;
			$sign['Nonce'] = $rand;
			if(!empty($time)){
				$sign['RemainTime'] = $time;
			}
            $sign['InstanceIds.0'] = $ins;
            $sign['Region'] = $selectregion;
            $sign['SecretId'] = $scid;
            $sign['Timestamp'] = time();
            $sign['Version'] = '2017-03-12';
    } else if ($act == 'AssociateSecurityGroups') {
            $sign['Action'] = $act;
			$sign['Nonce'] = $rand;
            $sign['InstanceIds.0'] = $ins;
            $sign['Region'] = $selectregion;
			$sign['SecurityGroupIds.0'] = $imageid;
            $sign['SecretId'] = $scid;
            $sign['Timestamp'] = time();
            $sign['Version'] = '2017-03-12';
    }
    ksort($sign);
    $url = '';
    foreach ($sign as $k => $v) {
        if (empty($url)) {
            $url = $k . '=' . $v;
        } else {
            $url = $url . '&' . $k . '=' . $v;
        }
    }
	$rs=array();
	$rs['sign']=array();
	$rs['url']=$url;
	$rs['sign']=$sign;
    return $rs;
}
function getsign($url)
{
    global $sckey;
    $v = 'GETcvm.tencentcloudapi.com/?' . $url;
    $sign = base64_encode(hash_hmac('sha1', $v, $sckey, true));
    return urlencode($sign);
}
function getit($array,$s)
{
    global $scid;
	$array['Signature']=$s;
	ksort($array);
	$url = '';
    foreach ($array as $k => $v) {
        if (empty($url)) {
            $url = $k . '=' . $v;
        } else {
            $url = $url . '&' . $k . '=' . $v;
        }
    }
    return file_get_contents('https://cvm.tencentcloudapi.com/?'.$url);
}
function requestins($act, $ins)
{
	$a=geturl($ins, $act,'','','','');
    $u = $a['url'];
    $s = getsign($u);
    return getit($a['sign'],$s);
}
function askpriceins($act,$img,$pass,$instype)
{
	$a=geturl('', $act,$img,$pass,$instype,'');
    $u = $a['url'];
    $s = getsign($u);
    return getit($a['sign'],$s);
}
function createins($act,$img,$pass,$instype,$t)
{
	$a=geturl('', $act,$img,$pass,$instype,$t);
    $u = $a['url'];
    $s = getsign($u);
	$bk=getit($a['sign'],$s);
    return $bk;
}
function delins($act, $ins,$time)
{
	$a=geturl($ins, $act,'','','',$time);
    $u = $a['url'];
    $s = getsign($u);
    $bk=getit($a['sign'],$s);
	file_put_contents('./TerminateReturn.txt',$bk);
	return $bk;
}
function setsafeins($act, $ins,$id)
{
	$a=geturl($ins, $act,$id,'','','');
    $u = $a['url'];
    $s = getsign($u);
    return getit($a['sign'],$s);
}
function updatestatu($msg)
{
    file_put_contents('./create.progress', $msg);
    /*Update Progress.*/
    if (!file_exists('./logs.txt')) {
        file_put_contents('./logs.txt', '');
    }
    $handle = fopen('./logs.txt', "a+");
    $str = fwrite($handle, "[" . date('Y-m-d') . "]-[" . date("h:i:sa") . "]-[$msg]\n");
    fclose($handle);
}
function grc($length) {
    $str = null;
    $strPol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    $max = strlen($strPol) - 1;
    for ($i = 0;$i < $length;$i++) {
        $str.= $strPol[rand(0, $max) ];
    }
    return $str;
}
function cli(){
return preg_match("/cli/i", php_sapi_name()) ? true : false;
}