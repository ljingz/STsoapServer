#!/usr/bin/env python
# -*- coding:utf-8 -*-

from suds.client import Client

client = Client('http://work/stsoapserver/demo/server.php?wsdl')
client.set_options(cache=None)

print client;
print client.service.method1(1, 5)
print client.service.method2()
print client.service.method3()
print client.service.method4()
print client.service.method5({"param":["stnts", u"国际企业中心"]})
print client.service.method6({"name":"stnts", "address":u"国际企业中心"})
print client.service.method7()
print client.service.method8()
