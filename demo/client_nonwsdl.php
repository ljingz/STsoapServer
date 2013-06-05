<?php
try {
    $client = new SoapClient(null, array(
    	"location"=>"http://work/stsoapserver/demo/server.php",
    	"uri"=>"http://test/",
    	"trace" => 1
    ));
    
    echo "<br />";
    print_r($client->method1(1, 5));
    
    echo "<br />";
	print_r($client->method2());
	
	echo "<br />";
	print_r($client->method3());
	
	echo "<br />";
	print_r($client->method4());
	
	echo "<br />";
	$param = new stdClass();
	$param->param = array("stnts", "国际企业中心");
	print_r($client->method5($param));
	
	echo "<br />";
	$param = new stdClass();
	$param->name = "stnts";
	$param->address = "国际企业中心";
	print_r($client->method6($param));
	
	echo "<br />";
	print_r($client->method7());
	
	echo "<br />";
	var_dump($client->method8());
	
} catch (SoapFault $fault){
    echo "Error: ",$fault->faultcode,", string: ",$fault->faultstring;
}