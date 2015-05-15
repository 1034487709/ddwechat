<?php
/**
*	微信类，集成微信常用功能,目前只支持明文模式
*	@author DragonDean
*	@url	http://www.dragondean.cn
*/
class ddwechat{
	public $appid;
	public $appscret;
	public $token;
	public $accesstoken;
	protected $xmlStr;	//xml大小写对应配置
	protected $http;	//http操作对象
	public $data;	//微信服务器推送来的数据
	public $errmsg; //错误信息
	
	
	/**
	*	构造函数，通过数组初始化参数
	*	@param array $param 数组格式的参数
	*/
	public function ddwechat($param){
		$this->__construct($param);
	}
	
	public function __construct($param = null){
		if(is_array( $param)){
			foreach($param as $key => $val){
				$this->setParam($key, $val);
			}
		}
	}
	
	/**
	*	输出错误信息
	*/
	public function showError(){
		echo "<b style=' color:red;'>".$this->errmsg."</b>";
	}
	
	/**
	*	设置参数
	*	@param string $name 参数名
	*	@param mixed $value 值
	*/
	public function setParam($name, $value){
		$this->$name = $value;
	}
	
	/**
	*	API接口验证
	*/
	public function validate(){
		if($this->checkSignature() && isset($_GET['echostr'])){
			echo $_GET['echostr'];
		}
	}
	
	/**
	*	检查签名
	*	@return bool 
	*/
	private function checkSignature(){
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		if( $tmpStr == $signature ){
			 return true;
		}else{
			$this->errmsg = "签名验证失败！";
			 return false;
		}
    }
	
	/**
	*	获取微信推送来的xml数据并保存到data中
	*/
	public function request(){
		if($_SERVER['REQUEST_METHOD'] != 'POST'){
			$this->errmsg = "请求方式不对！";
			return false;
		}
		$xml = file_get_contents("php://input");	
		$xml = new SimpleXMLElement($xml);
		
		if(!is_object($xml)){
			$this->errmsg = "xml数据接收错误";
			return false;
		}
		foreach ($xml as $key => $value) {
			$this->data[strtolower($key)] = strval($value);
		}
		return $this->data;
	}
	
	/**
	*	回复被动消息
	*	@param array $msg 回复的消息内容数组
	*/
	public function response($msg){
		if(empty($msg['msgtype']))return false;
		empty($msg['tousername']) && $msg['tousername'] = $this->data['fromusername'];
		empty($msg['fromusername']) && $msg['fromusername'] = $this->data['tousername'];
		empty($msg['createtime']) && $msg['createtime'] = time();
		echo $this->arr2xml($this->walkxmlvar($msg));
	}
	
	/**
	*	将数组转换为xml
	*	@param array $data	要转换的数组
	*	@param bool $root 	是否要根节点
	*	@return string 		xml字符串
	*	@link http://www.cnblogs.com/dragondean/p/php-array2xml.html
	*/
	private arr2xml($data, $root = true){
		$str="";
		if($root)$str .= "<xml>";
		foreach($data as $key => $val){
			//去掉key中的下标[]
			$key = preg_replace('/\[\d*\]/', '', $key);
			if(is_array($val)){
				$child = $this->arr2xml($val, false);
				$str .= "<$key>$child</$key>";
			}else{
				$str.= "<$key><![CDATA[$val]]></$key>";
			}
		}
		if($root)$str .= "</xml>";
		return $str;
	}
	
	/**
	*	根据xmlStr.config.php的配置，将小写变量转为微信服务要求的首字母大写
	*	@param string $str	要转换的字符串
	*	@return string $rt	转换后的字符串，没有原样返回
	*/	
	private function getxmlvar($str){
		if(!is_array($this->xmlStr))$this->xmlStr = require "xmlStr.config.php";
		if( !empty($this->xmlStr[$str]))return $this->xmlStr[$str];
		else return $str;
	}
	
	/**
	*	递归将数组中的小写变量转为微信需要的首字母大写格式
	*	@param array 要转换的数组
	*	@return array 转换后的数组
	*/
	private function walkxmlvar($arr){
		if(!is_array($arr))return array();
		$newArr = array();
		foreach($arr as $key => $val){
			if(is_array($val)){
				$newArr[$this->getxmlvar($key)] = $this->walkxmlvar($val);
			}else {
				$newArr[$this->getxmlvar($key)] = $val;
			}
		}
		return $newArr;
	}
	
	/**
	*	根据appid和appsecret获取accesstoken
	*	@param string $appid 
	*	@param string $appsecret
	*/
	public function getaccesstoken($appid = null, $appsecret = null){
		$appid || $appid = $this->appid;
		$appsecret || $appsecret = $this->appsecret;
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
		$temp = $this->exechttp($url);
		if($temp){
			$this->accesstoken = $temp['access_token'];
		}
		return $temp;
	}
	
	/**
	*	获取微信服务器IP地址
	*	@param string $accesstoken 可选
	*/
	public function getwechatip($accesstoken = null){
		$accesstoken || $accesstoken = $this->accesstoken;
		$url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=".$accesstoken ;
		return $this->exechttp($url);
	}
	
	/**
	*	添加/修改/删除客服账号,每个公众号最多添加10个客服账号
	*	@param  string $kfaccount 客服账号
	*	@param  string $nickname	客服昵称,最长6个汉字或12个英文字符
	*	@param  string $password	密码
	*	@param  string $action 		操作，默认是添加(add)，可选修改(update)或者删除(del)
	*	@param  string $accesstoken	可选参数
	*/
	public function kfaccount($kfaccount, $nickname, $password, $action = 'add' , $accesstoken = null){
		$accesstoken || $accesstoken = $this->accesstoken;
		$data = array( 'kf_account' => $kfaccount, 'nickname' => $nickname, 'password' => $password );
		$data = json_encode($data);
		$url = "https://api.weixin.qq.com/customservice/kfaccount/$action?access_token=".$accesstoken;
		return $this->exechttp($url, 'post', $data);
	}
	
	/**
	*	设置客服头像
	*/
	public function uploadkfhead(){
		//TODO
		return false;
	}
	
	/**
	*	获取所有客服账号
	*	@param string $accesstoken 可选参数
	*/
	public function getkflist($accesstoken = null){
		$accesstoken || $accesstoken = $this->accesstoken;
		$url = "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=".$accesstoken;
		return $this->exechttp($url);
	}
	
	/**
	*	发送客服消息
	*	@param array $msg 消息体数组
	*	@param string $accesstoken  可选参数
	*/
	public function custommsg($msg, $accesstoken = null){
		$accesstoken || $accesstoken = $this->accesstoken;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$accesstoken;
		return $this->exechttp($url, 'post', json_encode($data));
	}
	
	
	/**
	*	返回http操作对象，避免多次include和new
	*/
	private function gethttp(){
		if(!$this->http){
			include "ddhttp.class.php";
			$this->http = new ddhttp;
		}
		return $this->http;
	}
	
	/**
	*	执行http请求数据并分析结果，默认是get方式，如果错误则返回false，否则返回json_decode后的数组
	*	@param string $url	要请求的数组
	*	@param string $method 请求方式get或者post
	*	@param mixed $data	数据
	*/
	private function exechttp($url, $method = 'get',$data = null){
		$method = strtolower($method);
		$http = $this->gethttp();
		$temp = $http->$method($url, $data);

		if(!$temp){ //HTTP操作错误
			$this->errmsg = $http->errmsg;
			return false;
		}
		$tempArr = json_decode($temp, 1);
		if(!isset($tempArr['errcode']) || $tempArr['errcode'] != '0'){
			return $tempArr;
		}else {
			$this->errmsg = $tempArr['errmsg']."(代码：".$tempArr['errcode'].")";
			return false;
		}
	}
	
}
?>