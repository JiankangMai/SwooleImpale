<?php
require_once  './Bootstrap.php';
//$rst=\BromineMai\SwooleImpale\Util\Script::parseCliParam(['aaaa'=>2]);
//var_dump($rst);
$param=\BromineMai\SwooleImpale\Util\Script::parseCliParam(['type']);
switch ($param['type']){
    case 'client':
        $configClient=array_merge(require CONF_DIR.'client.php' ,require CONF_DIR.'pack.php' );
        $serv=new \BromineMai\SwooleImpale\Client\Client();
        $serv->init($configClient)->start();
        break;
    case 'server':
        $configServer=array_merge(require CONF_DIR.'server.php' ,require CONF_DIR.'pack.php' );
        $serv=new \BromineMai\SwooleImpale\Server\Server();
        $serv->init($configServer)->start();
        break;
    default:
        echo 'type参数缺失 --type=client 或 --type=server';
}