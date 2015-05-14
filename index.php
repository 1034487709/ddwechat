<?php

include "ddwechat.class.php";


//初始化
$config = array(
	'appid' => 'wxbdadf92089d8a8fe',
	'appsecret'	=> '60703a01783f11664c718df5f568b2c5',
	'token'	=>	'dragondean'
);
$dd = new ddwechat($config);

var_dump($dd->getaccesstoken());
var_dump($dd->getwechatip());
//设置参数
$dd->setParam('createtime',time());

//验证消息来源
if(isset($_GET['echostr'])){
	$dd->validate();
	exit;
}

//获取微信服务推送的xml信息,所有变量都会自动变为小写的
$data = $dd->request();
if(!$data)die('数据获取失败，可能是请求方式不对');

if($data['msgtype'] == 'text')$dd->response(array('msgtype'=>"text", 'content'=>"你发的内容是：".$data['content']));
else if($data['msgtype'] == 'image') $dd->response(array('msgtype'=>"text", 'content'=>"发你妹的图片"));
else if($data['msgtype'] == 'location') $dd->response(array('msgtype'=>"text", 'content'=>"我不想知道你在哪"));
else $dd->response(array('msgtype'=>"text", 'content'=>"我去，这你也敢发啊"));



//输出错误信息
#echo $dd->showError();

//哎，太菜了。欢迎各位大神来指正
