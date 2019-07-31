<?php
/**
 * 客户端配置  具体含义和默认值见对应的配置类
 */
return [
  'tcpConfig'=>new \BromineMai\SwooleImpale\Config\TcpClientConfig([
      'ip'=>'127.0.0.1',
      //'ip'=>'39.98.160.161',
      'port'=>'9501'
  ]),
    'httpServerList'=>[
        new \BromineMai\SwooleImpale\Config\HttpServerConfig([
            'ip'=>'127.0.0.1',
            'port'=>'80',
            'domain'=>'inter.bromine.cn',
        ]),
        new \BromineMai\SwooleImpale\Config\HttpServerConfig([
            'ip'=>'127.0.0.1',
            'port'=>'82',
            'domain'=>'inter2.bromine.cn',
        ]),

    ]
];