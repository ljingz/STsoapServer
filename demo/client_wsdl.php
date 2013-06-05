<?php
try {
    $client = new SoapClient("http://work/stsoapserver/demo/server.php?wsdl", array(
    	'cache_wsdl' => WSDL_CACHE_NONE,
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
	print_r($client->method5(array("stnts", "国际企业中心")));
	
	echo "<br />";
	print_r($client->method6(array("name"=>"stnts", "address"=>"国际企业中心")));
	
	echo "<br />";
	print_r($client->method7());
	
	echo "<br />";
	var_dump($client->method8());
	
} catch (SoapFault $fault){
    echo "Error: ",$fault->faultcode,", string: ",$fault->faultstring;
}