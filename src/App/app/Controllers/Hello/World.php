<?php
class HelloWorldController extends BaseController {
	public function __init() {
		$this->checkRule = array(
			'uid'   => "num|0,10|*|10|uid参数非法",
			'type'  => "enum|0,1,2|NULL|2|类型只能为1,2,3",
			'name'  => "string|10,30|*|10|用户名参数非法",
			'url'   => "custom|url|NULL|http://www.baidu.com|url不是合法的url",
			'test'  => "string|0,100|NULL|qidassheng|test参数",
		);
	}


	public function run() {
		//echo '<pre>';
		//print_r($_SERVER);
		//echo 'Hello word! I am qi da sheng!';
		$Data_Hello_World = new Data_Hello_World();
		$info = $Data_Hello_World->get_data();
		$data['code'] = '100000';
		$data['msg']  = '操作成功';
		$data['data'] = $info;
		$this->displayJson($data);
		//$HelloQidashengData = new HelloQidashengData(); 
		//echo $HelloQidashengData->getData();

		//$psrTestObj = new Test\Hello\World();
		//echo $psrTestObj->a();
		//print_r($this->request->all());
		//print_r($this->request->server('REMOTE_ADDR'));
		//print_r($this->request->server('qsf', time()));
	}
}

