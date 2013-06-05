<?php
/**
 * STsoapServer:
 * 继承自SoapServer，用于自动生成wsdl文档
 * @author liuj
 */

class STsoapServer extends SoapServer {
	protected $options = array();                   //SoapServer-options
	protected $functions = array();                 //SoapServer-functions
	protected $object = null;                       //SoapServer-object
	protected $wsdl = array();                      //wsdl参数
	
	public function __construct($options=array()){
		if(array_key_exists("wsdl", $options)){
			$this->wsdl = $options["wsdl"];
			unset($options["wsdl"]);
		}
		$this->options = $options;
	}
	
	public function addFunction($function){
		if($function instanceof ReflectionFunctionAbstract){
			$this->functions[$function->getName()] = $function;
		}else{
			if($function==SOAP_FUNCTIONS_ALL){
				$functions = get_defined_functions();
				foreach($functions["user"] as $function){
					$function = new ReflectionFunction($function);
					if($function->getFileName()==$_SERVER["SCRIPT_FILENAME"]){
						$this->addFunction($function);
					}
				}
			}elseif(function_exists($function)){
				$this->addFunction(new ReflectionFunction($function));
			}
		}
	}
	
	public function setClass($class){
		$this->setObject(new $class);
	}
	
	public function setObject($object){
		if($this->object===null){
			$this->object = $object;
			//取得所有函数
			$reflection = new ReflectionObject($object);
			foreach($reflection->getMethods() as $method){
				if($method->isPublic()&&!$method->isConstructor()){
					$this->addFunction($method);
				}
			}
			//取得类中定义的wsdl数组
			if($this->wsdl!==false){
				if($object->wsdl!==false){
					if(is_array($object->wsdl)){
						if(is_array($this->wsdl)){
							$this->wsdl = array_merge($object->wsdl, $this->wsdl);
						}else{
							$this->wsdl = $object->wsdl;
						}
					}
					//设置serviceName
					if(empty($this->wsdl["__serviceName"])){
						$this->wsdl["__serviceName"] = $reflection->getName();
					}
				}else{
					if(empty($this->wsdl)){
						$this->wsdl = $object->wsdl;
					}
				}
			}
		}
	}
	
	public function handle($request=null){
		//获取wsdl文件
		$wsdlFile = $this->getWsdlFile();
		//调用SoapServer
		parent::__construct($wsdlFile, $this->options);
		if($this->functions){
			foreach($this->functions as $function){
				if($function instanceof ReflectionFunction){
					parent::addFunction($function->getName());
				}
			}
		}
		if($this->object){
			parent::setObject($this->object);
		}
		if($request===null){
			global $HTTP_RAW_POST_DATA;
			if(isset($HTTP_RAW_POST_DATA)) {
				$request = $HTTP_RAW_POST_DATA;
			}else {
				$request = file_get_contents("php://input");
			}
		}
		parent::handle($request);
	}
	
	protected function getWsdlFile(){
		//不启用wsdl模式
		if($this->wsdl===false){
			return null;
		}else{
			//设置serviceName
			if(empty($this->wsdl["__serviceName"])){
				$this->wsdl["__serviceName"] = "stnts";
			}
			//设置location
			if(empty($this->wsdl["__location"])){
				$this->wsdl["__location"] = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
			}
			//生成wsdl文件至目录
			if(empty($this->wsdl["__path"])){
				$this->wsdl["__path"] = "/tmp/";
			}
			//生成wsdl文档类
			require("STwsdlGenerate.class.php");
			$generateObject = new STwsdlGenerate($this->functions, $this->wsdl);
			//debug状态或文件不存在，重新生成wsdl文档
			$wsdlFile = $this->wsdl["__path"].$generateObject->getFileName();
			if($this->wsdl["__cache"]===false||!file_exists($wsdlFile)){
				//检查目录是否可写
				if(!is_writable($this->wsdl["__path"])){
					throw new Exception(sprintf("目录 %s 不可写", $this->wsdl["__path"]));
				}
				//生成wsdl文档类
				$wsdlDoc = $generateObject->generate();
				if(!file_put_contents($wsdlFile, $wsdlDoc)){
					throw new Exception("创建wsdl文件失败");
				}
			}
			return $wsdlFile;
		}
	}
}