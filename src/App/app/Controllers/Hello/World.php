<?php
class HelloWorldController extends BaseController {

	public function run() {
		echo '<pre>';
		//print_r($_SERVER);
		echo 'Hello word! I am qi da sheng!';
		$Data_Hello_World = new Data_Hello_World();
		//echo $Data_Hello_World->get_data();
		$HelloQidashengData = new HelloQidashengData(); 
		echo $HelloQidashengData->getData();

		$psrTestObj = new Test\Hello\World();
		echo $psrTestObj->a();
		print_r($this->request->all());
		print_r($this->request->server('REMOTE_ADDR'));
		print_r($this->request->server('qsf', time()));
	}
}

