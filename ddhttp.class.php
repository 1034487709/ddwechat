<?php
/**
*	http操作类,实现简单的GET和POST操作
*	@author Dragondean
*	@date 2015-05-14
*/
class ddhttp{
	protected $url;	//要请求的地址
	protected $method;	//请求方法
	protected $data;	//数据
	public $errmsg;		//错误信息,返回false的时候可以用来获取详细的错误代码
	
	/**
	*	GET方式抓取数据
	*	@param string $url 要抓取的URL
	*/
	public function get($url, $data = null){
		$this->url = $url;
		$data && $this->data = $data;
		$this->method = "GET";
		return $this->excRequest();
	}
	
	/**
	*	POST提交数据并返回内容
	*	@param string $url 要请求的地址
	*	@param mixed $data 提交的数据
	*/
	public function post($url, $data = null){
		$this->url = $url;
		$this->method = "POST";
		$this->data = $data;
		return $this->excRequest();
	}
	
	/**
	*	执行请求并返回数据
	*	@access private 
	*/
	private function excRequest(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$tmpInfo = curl_exec($ch);
		$errorno = curl_errno($ch);
		if(!$errorno)return $tmpInfo;
		else{
			$this->errmsg = $errorno;
			return false;
		}
	}
}
?>