<?php

include "ddwechat.class.php";

//初始化
$config = array(
	'appid' => 'yourappid',
	'appsecret'	=> 'yousecret',
	'token'	=>	'token',
	'other' => 'other info'
);
$dd = new ddwechat($config);

//设置参数
$dd->setParam('createtime',time());

//验证消息来源
if($_GET['echostr']){
	$dd->validate();
	exit;
}

//获取微信服务推送的xml信息,所有变量都会自动变为小写的
$data = $dd->request();

//回复被动消息（text类型）,变量名使用小写，回复的时候会自动转换对应的首字母大写格式，在xmlStr.config.php中配置好了对应关系
$msg=array(
	'msgtype' => 'text',
	'content'	=> '我就是个测试的消息'
);
$dd->response($msg);

//输出错误信息
echo $dd->showError();

//哎，太菜了。欢迎各位大神来指正
