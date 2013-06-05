<?php
//测试server

class server {
	public $wsdl = array(
		"__cache"        => false,
		"__path"         => "/tmp/",
		"__serviceName"  => "demo",
		"__location"     => "http://work/stsoapserver/demo/server.php",
		"method1"=>array(
			"_param"=>array("int", "int"),
			"_return"=>"int"
		),
		"method2"=>array(
			"_return"=>"string"
		),
		"method3"=>array(
			"_return"=>array("string")
		),
		"method4"=>array(
			"_return"=>array("name"=>"string", "address"=>"string")
		),
		"method5"=>array(
			"_param"=>array(array("string")),
			"_return"=>"string"
		),
		"method6"=>array(
			"_param"=>array(array("name"=>"string", "address"=>"string")),
			"_return"=>"string"
		),
		"method7"=>array(
			"_return"=>array(array("name"=>"string", "address"=>"string"))
		),
		"method8"=>array(
			"_return"=>"boolean"
		)
	);
	
	public function method1($num1, $num2){
		return $num1 + $num2;
	}
	
	public function method2(){
		return "stnts国际企业中心";
	}
	
	public function method3(){
		return array("stnts", "国际企业中心");
	}
	
	public function method4(){
		$class = new stdClass();
		$class->name = "stnts";
		$class->address = "国际企业中心";
		return $class;
	}
	
	public function method5($array){
		return implode(",", $array->param);
	}
	
	public function method6($object){
		return $object->name.$object->address;
	}
	
	public function method7(){
		return array(
			array("name"=>"stnts", "address"=>"国际企业中心"),
			array("name"=>"alibaba", "address"=>"阿里巴巴集团"),
		);
	}
	
	public function method8(){
		return true;
	}
}

try{
	require("../STsoapServer/STsoapServer.class.php");
	$soap = new STsoapServer(array(
		'uri'=>"http://localhost/"
	));
	$soap->setClass('server');
	$soap->handle();
}catch(Exception $e){
	echo $e->getMessage();
}