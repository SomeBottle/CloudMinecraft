# CloudMinecraft
Create a minecraft server with TencentCloud API when the worlds are saved in another Student's Cloud.  
![](https://ww2.sinaimg.cn/large/ed039e1fgy1fzvoozfnkwj20m808c77v)  

* Example Video: https://storage.xbottle.top/v/CloudMinecraft.mp4  

## 简述  

   这是一个基于腾讯云API的简单**低成本开服方案**的实现，起初的想法来自于Best33：https://best33.com/179.moe  
另外在这个项目产生前一天，Ghosin已经创造了对接了机器人的<del>PY</del>Python版：https://github.com/Ghosin/dejavu  
我在用PHP写的时候确实遇到了一些麻烦，感谢众人的指导.  

   实现原理要说难也不难.首先是前台(可以对接机器人)调用**控制服务器**上的CloudMC API(main.php)，由此发出请求通过QcloudAPI创建
云服务器(按量计费)并通过php的扩展连接ssh，通过sftp传输**打包存档**并进行解压、部署，运行Minecraft服务器.达到条件关闭服务器的时候，  
远程向screen发送指令停止MC服务器并进行**打包**传回**控制服务器**.控制服务器最后释放掉该按量付费服务器。  

   其中的文件传输全靠同一区域的内网传输（用外网怕不是吃一堆流量，还很慢）.  

## 应用要求  

1. **有一台**腾讯云(最好是学生云)服务器.  
2. 你想开一个Minecraft基友服而不是大服（QCloud很贵的）.  

## 环境要求  

* 安装PHP SSH2扩展(好似PHP版本>=7.0才有?)  
* 启用函数exec();  

## 配置  

* 在**conf.php**中进行基本配置，包括：
  1. 访问的授权key.  
  2. 腾讯云的API secret id和key以及其他实例创建配置.  
  3. 服务器释放/保留时间配置.  
  4. 配置的区域一定要和你**已有的**一台云的区域一样(这样内网才互通).
  
* 将目录下所有文件上传到**已有的**一台云上的**网站目录**（main.php是在非CLI模式执行的）.  
* 访问DemoPage内的server.html或者机器人对接main.php,开始云上Minecraft.  
* 请**务必**在网站服务器**设置不可访问**server.json，在创建服务器时server.json会用来储存必要的信息！  

## 对接main.php  

* 向main.php发出post请求：  

  * 请求的键.  
  
  |  请求键  |  值  |
  |:--------:|:----:|
  |  key  |  访问的授权key  |
  | command |  发送的指令  |
  
  * 请求的url参数**?action=**.
  
  | Action | post要携带的键值 | 作用 |
  |:-:|:-:|:-:|
  | create | key | 开始执行创建服务器 |
  | progress | key | 查看服务器的执行状态 |
  | skip | key | 中止目前正在执行的任务(可以用来关服) |
  | sendcommand | key , command | 发送特定的指令(不带/)至Minecraft服务器 |
  
  * 例如我想创建新的服务器，带上key值post main.php?action=create,详情看DemoPage源码.  
  
## 感言  

这回研究API真的花费了我很多时间，但也是我第一次尝试对接服务商API.该项目耗费了我两整天，可能代码不精，有问题望发issue.  

* 联系：somebottle@qq.com   