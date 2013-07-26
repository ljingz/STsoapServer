入门
-----------------------------------

STsoapServer类继承自SoapServer，用于生成wsdl文档，使用简单方便；
    
    require("STsoapServer/STsoapServer.class.php");
     
    class server {
        public function hello(){
            return "hello word";
        }
    }
     
    try{
        $soap = new STsoapServer(array(
            "uri"=>"http://localhost/"
        ));
        $soap->setClass("server");
        $soap->handle();
    }catch(Exception $e){
        echo $e->getMessage();
    }

高级
-----------------------------------

SoapServer类WSDL模式要求必须传入一个已存在的wsdl文档作为参数，STsoapServer会在某目录下生成wsdl文档，将地址作为参数传入；

生成wsdl文件需要一些参数配置，有以下两个地方可以配置此参数：

实例化STsoapServer类时增加wsdl项：

    new STsoapServer(array(
        "uri"=>"http://localhost/",
        "wsdl"=>array(
            //options
        )
    ));

在类中定义wsdl属性：

    class server {
        public $wsdl = array(
            //options
        );
    }

#### 基本配置

每次调用服务都重新根据方法或函数来生成wsdl文件显然是极其低效的，STsoapServer默认会缓存wsdl文件，直到文件被手动删除，但在开发过程中这种行为会造成不便，STsoapServer允许配置关闭缓存)：

    class server {
        public $wsdl = array(
            "__cache"=>false
        );
    }

WSDL文件的保存目录，缺省值为”/tmp/“：

    class server {
        public $wsdl = array(
            "__cache"=>false,
            "__path"=>"/tmp/"
        );
    }

soap服务名称，缺省值为类名称，不存在类则为”stnts”：

    class server {
        public $wsdl = array(
            "__cache"=>false,
            "__path"=>"/tmp/",
            "__serviceName"=>"test"
        );
    }

soap服务地址，缺省值为当前url地址：

    class server {
        public $wsdl = array(
            "__cache"=>false,
            "__path"=>"/tmp/",
            "__serviceName"=>"test",
            "__location"=>"http://example.com/service.php"
        );
    }

#### 类型配置

PHP为弱类型语言，程序无法自动分辨出参数与返回值的数据类型，在与其他语言进行服务调用的时候会出现不可预料的情况；
在wsdl数组中添加方法或函数的同名项定义数据类型：

两个参数为数字，返回值为数字：

    //function
    function method1($num1, $num2){
        return $num1 + $num2;
    }
    //options
    $wsdl = array(
        "method1"=>array(
            "_param"=>array("int", "int"),
            "_return"=>"int"
        )
    );

无参数，返回值为字符串：

    //function
    function method2(){
        return "stnts hello";
    }
    //options
    $wsdl = array(
        "method2"=>array(
            "_return"=>"string"
        )
    );

无参数，返回值为数组：

    //function
    function method3(){
        return array("stnts", "hello");
    }
    //options
    $wsdl = array(
        "method3"=>array(
            "_return"=>array("string")
        )
    );

无参数，返回值为对象：

    //function
    function method4(){
        $class = new stdClass();
        $class->name = "stnts";
        $class->address = "hello";
        return $class;
    }
    //options
    $wsdl = array(
        "method4"=>array(
            "_return"=>array("name"=>"string", "address"=>"string")
        )
    );

参数为数组，返回值为字符串：

    //function
    function method5($array){
        return implode(",", $array->param);
    }
    //options
    $wsdl = array(
        "method5"=>array(
            "_param"=>array(array("string")),
            "_return"=>"string"
        )
    );
    
    注：SOAP规范中Array类型的兼容性很不好，使用了自定义类型来表示数组，以至于需要获取param属性才能获取到数组参数；
    
参数为对象，返回值为字符串：

    //function
    function method6($object){
        return $object->name.$object->address;
    }
    //options
    $wsdl = array(
        "method6"=>array(
            "_param"=>array(array("name"=>"string", "address"=>"string")),
            "_return"=>"string"
        )
    );

无参数，返回值为一个标准数据库列表：

    //function
    function method7(){
        return array(
            array("name"=>"stnts", "address"=>"hello"),
            array("name"=>"alibaba", "address"=>"hi"),
        );
    }
    //options
    $wsdl = array(
        "method7"=>array(
            "_return"=>array(array("name"=>"string", "address"=>"string"))
        )
    );

无参数，返回值为布尔类型：

    //function
    function method8(){
        return true;
    }
    //options
    $wsdl = array(
        "method8"=>array(
            "_return"=>"boolean"
        )
    );

