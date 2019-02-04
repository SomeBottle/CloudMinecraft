<?php
$keys=array(
  1=>'your own key'
);/*访问授权key*/
$secrets=array(
  id=>'',
  key=>''
);/*API SECRET ID&KEY*/
$prepareins = array(
   1 => 'SA1.SMALL1',
   2 => 'S1.SMALL1',
   3 => 'S2.SMALL1'
);/*实例型号备选*/
$selectregion='ap-beijing';/*地区选择*/
$selectzone='ap-beijing-1';/*Placement.Zone可用区选择*/
$disksize=50;/*磁盘大小*/
$imageid='img-dkwyg6sr';/*选择安装的镜像id*/
$waitfordestroy=40;/*等待多少(分钟)自动释放(保险用)*/
$allownobody=10;/*服务器部署后允许几(分钟)没人(>=10)*/
$ziptime=60;/*留给解压的时间(秒)*/
$secugroupid='';/*绑定的安全组id*/
?>