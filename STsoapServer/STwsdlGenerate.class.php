<?php
/**
 * STwsdlGenerate:
 * 生成SoapServer的wsdl文档
 * @author liuj
 */

class STwsdlGenerate {
	protected $serviceName = null;             //soap服务名称
	protected $location = null;                //soap服务地址
	protected $functions = array();            //soap函数
	protected $wsdl = array();                 //wsdl参数
	protected $wsdlDoc = array();              //wsdl文档
	
	public function __construct($functions, $wsdl){
		$this->functions = $functions;
		$this->wsdl = $wsdl;
		$this->serviceName = $this->wsdl["__serviceName"];
		$this->location = $this->wsdl["__location"];
	}
	
	public function getTypes($function){
		$paramTypes = $this->parseParamType($function);
		foreach($function->getParameters() as $param){
			$paramType = $paramTypes[$param->getName()];
			if(is_array($paramType)){
				$type .= $this->buildComplexType($paramType, sprintf("%s%sParam", $function->getName(), ucwords($param->getName())), "param");
			}
		}
		$returnTypes = $this->paraseReturnType($function);
		if(is_array($returnTypes)){
			$type .= $this->buildComplexType($returnTypes, sprintf("%sResult", $function->getName()), "result");
		}
		return $type;
	}
	
	private function parseParamType($function){
		$type = $this->wsdl[$function->getName()]["_param"];
		if(!empty($type)){
			if(is_string($type)){
				$type = array($type);
			}
			if(count($type)>0){
				$parameters = $function->getParameters();
				if(count($type)>1&&count($type)!=count($parameters)){
					throw new Exception(sprintf("%s()函数参数类型数量错误", $function->getName()));
				}
				foreach($parameters as $key=>&$param){
					if(count($type)>1){
						$paramType[$param->getName()] = $type[$key];
					}else{
						$paramType[$param->getName()] = $type[0];
					}
				}
				return $paramType;
			}
		}
		return array();
	}
	
	private function paraseReturnType($function){
		$type = $this->wsdl[$function->getName()]["_return"];
		return $type;
	}
	
	private function buildComplexType($types, $typeName=null, $defaultElementName="arg"){
		if(empty($typeName)){
			$complexType = "<complexType>\r\n";
		}else{
			$complexType = sprintf("<complexType name=\"%s\">\r\n", $typeName);
		}
		$complexType .= "<sequence>\r\n";
		foreach($types as $name=>$type){
			if(is_numeric($name)){
				if(is_array($type)){
					$complexType .= sprintf("<element name=\"%s\" minOccurs=\"0\" maxOccurs=\"unbounded\" nillable=\"true\">\r\n%s</element>\r\n", $defaultElementName, $this->buildComplexType($type));
				}else{
					$complexType .= sprintf("<element name=\"%s\" minOccurs=\"0\" maxOccurs=\"unbounded\" nillable=\"true\" type=\"xsd:%s\"/>\r\n", $defaultElementName, $type);
				}
				break;
			}else{
				if(is_array($type)){
					$complexType .= sprintf("<element name=\"%s\" minOccurs=\"0\" nillable=\"true\">\r\n%s</element>\r\n", $name, $this->buildComplexType($type));
				}else{
					$complexType .= sprintf("<element name=\"%s\" minOccurs=\"0\" nillable=\"true\" type=\"xsd:%s\"/>\r\n", $name, $type);
				}
			}
		}
		$complexType .= "</sequence>\r\n";
		$complexType .= "</complexType>\r\n";
		return $complexType;
	}
	
	public function getMessage($function){
		$paramType = $this->parseParamType($function);
		foreach($function->getParameters() as $param){
			$partType = $paramType[$param->getName()];
			if(empty($partType)){
				$partType = "xsd:string";
			}else{
				if(is_array($partType)){
					$partType = sprintf("tns:%s%sParam", $function->getName(), ucwords($param->getName()));
				}else{
					$partType = sprintf("xsd:%s", $partType);
				}
			}
			$request .= sprintf("<part name=\"%s\" type=\"%s\"/>\r\n", $param->getName(), $partType);
		}
		$returnType = $this->paraseReturnType($function);
		if(empty($returnType)){
			$partType = "xsd:string";
		}else{
			if(is_array($returnType)){
				$partType = sprintf("tns:%sResult", $function->getName());
			}else{
				$partType = sprintf("xsd:%s", $returnType);
			}
		}
		$response = sprintf("<part name=\"%sResult\" type=\"%s\"/>\r\n", $function->getName(), $partType);
		if($request){
			$message = sprintf("<message name=\"%sRequest\">\r\n%s</message>\r\n", $function->getName(), $request);
		}else{
			$message = sprintf("<message name=\"%sRequest\"/>\r\n", $function->getName());
		}
		if($response){
			$message .= sprintf("<message name=\"%sResponse\">\r\n%s</message>\r\n", $function->getName(), $response);
		}else{
			$message .= sprintf("<message name=\"%sResponse\"/>\r\n", $function->getName());
		}
		return $message;
	}
	
	public function getPortType($function){
		return sprintf("<operation name=\"%s\" parameterOrder=\"arg\" >\r\n", $function->getName())
				. sprintf("<input message=\"wns:%sRequest\"/>\r\n", $function->getName())
				. sprintf("<output message=\"wns:%sResponse\"/>\r\n", $function->getName())
				. "</operation>\r\n";
	}
	
	public function getBinding($function){
		return sprintf("<operation name=\"%s\">\r\n", $function->getName())
				. sprintf("<soap:operation soapAction=\"http://tempuri.org/action/%s\"/>\r\n", $function->getName())
				. "<input>\r\n"
				. "<soap:body use=\"encoded\" namespace=\"http://tempuri.org/message/\" encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\" />\r\n"
				. "</input>\r\n"
				. "<output>\r\n"
				. "<soap:body use=\"encoded\" namespace=\"http://tempuri.org/message/\" encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\" />\r\n"
				. "</output>\r\n"
				. "</operation>\r\n";
	}
	
	public function functionHandle(){
		foreach($this->functions as $function){
			$this->wsdlDoc["types"] .= $this->getTypes($function);
			$this->wsdlDoc["message"] .= $this->getMessage($function);
			$this->wsdlDoc["portType"] .= $this->getPortType($function);
			$this->wsdlDoc["binding"] .= $this->getBinding($function);
		}
	}
	
	public function generate(){
		$this->functionHandle();
		return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n"
				. sprintf("<definitions name=\"%sApplication\" targetNamespace=\"http://tempuri.org/wsdl/\" xmlns:wns=\"http://tempuri.org/wsdl/\" xmlns:tns=\"http://tempuri.org/xsd/\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/wsdl/soap/\" xmlns:stk=\"http://schemas.microsoft.com/soap-toolkit/wsdl-extension\" xmlns=\"http://schemas.xmlsoap.org/wsdl/\">\r\n", $this->serviceName)
				. "<types>\r\n"
				. "<schema targetNamespace=\"http://tempuri.org/xsd/\" xmlns=\"http://www.w3.org/2001/XMLSchema\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\" xmlns:wsdl=\"http://schemas.xmlsoap.org/wsdl/\" elementFormDefault=\"qualified\">\r\n"
				. $this->wsdlDoc["types"]
				. "</schema>\r\n"
				. "</types>\r\n"
				. $this->wsdlDoc["message"]
				. "<portType name=\"STportType\">\r\n"
				. $this->wsdlDoc["portType"]
				. "</portType>\r\n"
				. "<binding name=\"STbinding\" type=\"wns:STportType\">"
				. "<stk:binding preferredEncoding=\"UTF-8\" />"
				. "<soap:binding style=\"rpc\" transport=\"http://schemas.xmlsoap.org/soap/http\"/>"
				. $this->wsdlDoc["binding"]
				. "</binding>"
				. sprintf("<service name=\"%sService\">\r\n", $this->serviceName)
				. sprintf("<port name=\"%sPort\" binding=\"wns:STbinding\">\r\n", $this->serviceName)
				. sprintf("<soap:address location=\"%s\"/>\r\n", $this->location)
				. "</port>\r\n"
				. "</service>\r\n"
				. "</definitions>";
	}
	
	public function getFileName(){
		foreach($this->functions as $function){
			$info .= $function->__toString();
		}
		return sprintf("STwsdl-%s-%s", $this->serviceName, md5($info));
	}
}