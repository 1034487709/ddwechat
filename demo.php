<?php

include "ddwechat.class.php";

//��ʼ��
$config = array(
	'appid' => 'yourappid',
	'appsecret'	=> 'yousecret',
	'token'	=>	'token',
	'other' => 'other info'
);
$dd = new ddwechat($config);

//���ò���
$dd->setParam('createtime',time());

//��֤��Ϣ��Դ
if($_GET['echostr']){
	$dd->validate();
	exit;
}

//��ȡ΢�ŷ������͵�xml��Ϣ,���б��������Զ���ΪСд��
$data = $dd->request();

//�ظ�������Ϣ��text���ͣ�,������ʹ��Сд���ظ���ʱ����Զ�ת����Ӧ������ĸ��д��ʽ����xmlStr.config.php�����ú��˶�Ӧ��ϵ
$msg=array(
	'msgtype' => 'text',
	'content'	=> '�Ҿ��Ǹ����Ե���Ϣ'
);
$dd->response($msg);

//���������Ϣ
echo $dd->showError();

//����̫���ˡ���ӭ��λ������ָ��
