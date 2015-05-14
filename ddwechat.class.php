<?php
/**
*	微信类，集成微信常用功能,目前只支持明文模式
*/
class ddwechat{
	public $appid;
	public $appscret;
	public $token;
	public $access_token;
	protected $xmlStr;	//xml大小写对应配置
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
	*	判断消息的真实性
	*/
	public function validate(){
		if($this->checkSignature() && isset($_GET['echostr'])){
			echo $_GET['echostr'];
		}
	}
	
	/**
	*	检查签名
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
	*/
	private function arr2xml($data, $root = true){
		$str="";
		if($root)$str .= "<xml>";
		foreach($data as $key => $val){
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
}
?>