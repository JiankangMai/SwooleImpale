<?php
return [
  'tcpConfig'=>new \BromineMai\SwooleImpale\Config\TcpListenConfig([
      'ip'=>'0.0.0.0',
      'port'=>'9501'
  ]),
    'webListenConfig'=>new \BromineMai\SwooleImpale\Config\WebListenConfig([
        'ip'=>'0.0.0.0',
        'port'=>'999'
    ]),
    
    'domainTableConfig'=>new \BromineMai\SwooleImpale\Config\DomainTableConfig([
        'fdLong'=>2,
        'domainLong'=>64,
        'domainCount'=>1024, 
    ]),
];