<?php
return [
  'tcpConfig'=>new \BromineMai\SwooleImpale\Config\TcpClientConfig([
      'ip'=>'127.0.0.1',
      'port'=>'9501'
  ]),
    'httpServerList'=>[
        new \BromineMai\SwooleImpale\Config\HttpServerConfig([
            'ip'=>'127.0.0.1',
            'port'=>'81',
            'domain'=>'inter.bromine.cn',
        ]),
        new \BromineMai\SwooleImpale\Config\HttpServerConfig([
            'ip'=>'127.0.0.1',
            'port'=>'82',
            'domain'=>'inter2.bromine.cn',
        ])   
    ]
];