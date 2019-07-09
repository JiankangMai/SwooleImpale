<?php
return [
  'tcpConfig'=>new \BromineMai\SwooleImpale\Config\TcpListenConfig([
      'ip'=>'127.0.0.1',
      'port'=>'9501'
  ]),
    'webListenConfig'=>new \BromineMai\SwooleImpale\Config\WebListenConfig([
        'ip'=>'0.0.0.0',
        'port'=>'84'
    ]),
    
    'domainTableConfig'=>new \BromineMai\SwooleImpale\Config\DomainTableConfig([
        'fdLong'=>2,
        'domainLong'=>64,
        'domainCount'=>1024, 
    ]),
  
];