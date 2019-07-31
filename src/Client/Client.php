<?php
namespace BromineMai\SwooleImpale\Client;
use BromineMai\SwooleImpale\Config\HttpServerConfig;
use BromineMai\SwooleImpale\Config\PackConfig;
use BromineMai\SwooleImpale\Config\TcpClientConfig;
use BromineMai\SwooleImpale\Msg\DataLoadTrait;
use BromineMai\SwooleImpale\Msg\HttpRequest;
use BromineMai\SwooleImpale\Msg\HttpResponse;
use BromineMai\SwooleImpale\Msg\Msg;
use BromineMai\SwooleImpale\Msg\Register;
use BromineMai\SwooleImpale\Msg\RegisterRst;
use BromineMai\SwooleImpale\Server\Service;
use BromineMai\SwooleImpale\Util\Packer;

class Client{
    use DataLoadTrait;
    use Service;
    
    private $state;
    const STATGE_STOPED=3;
    const STATGE_CONNECT=2;
    
    /** @var TcpClientConfig $tcpConfig */
    private $tcpConfig;
    /** @var  PackConfig $packConfig */
    public $packConfig;
    /**
     * @var HttpServerConfig[] $httpServerList
     */
    private $httpServerList;

    /**
     * @var Packer
     */
    private $packer;
    /**
     * @var \Swoole\Client $client
     */
    private $client;
    public function init(array $config){
        $this->loadData($config);
        $this->httpServerList=array_column($this->httpServerList,null,'domain');
        $this->packer=Packer::getInstance('client',$this->packConfig);
        return $this;
    }

    
    public function start(){
        $this->client=$client = new \Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
        
        $client->on("connect", function( $client){
            echo "connect\n";
            $this->state=self::STATGE_CONNECT;
            foreach ($this->httpServerList as $httpServer){
                $data=$this->packer->pack(new Register($httpServer->domain));
                if (!$client->send($data))
                {
                    die("send failed.");
                }
            }
        });
        
        $client->on("receive", function(\Swoole\Client $cli, $data){
            $data=$this->packer->unPack($data);
            //echo "Receive: $data".PHP_EOL;
            if (!$data) {
                die("recv failed.");
            }else{
                $rst=Msg::fromString($data);
                switch ($rst->type){
                    case Msg::MSG_TYPE_REGISTER_RESULT:
                        /** @var $rst RegisterRst */
                        if(RegisterRst::RST_TYPE_SUCCESS!=$rst->rstType){
                            die($rst->domain.'注册失败：'.$rst->getRstTypeStr($rst->rstType).PHP_EOL);
                        }else{
                            echo $rst->domain.'注册成功'.PHP_EOL;
                        }
                        break;
                    case Msg::MSG_TYPE_HTTP_REQUEST:
                        /** @var $rst HttpRequest */
                        echo 'for domain:'.$rst ->domain.PHP_EOL;
                        $serverConfig=$this->httpServerList[$rst ->domain];
                        if(!empty($serverConfig)){
                            $client = new \Swoole\Client(SWOOLE_SOCK_TCP);
                            if (!$client->connect($serverConfig->ip, $serverConfig->port, -1))
                            {
                                exit("connect failed. Error: {$client->errCode}\n");
                            }
                            $client->send($rst->httpData);
                            while(1){
                                $httpResult=$client->recv();
                                if(false === $httpResult){
                                    echo 'error_recv code:'.$client->errCode.PHP_EOL;
                                    $isEnd=true;
                                }else{
                                    $lastStr=substr($httpResult,-5,5);
                                    if("0\r\n\r\n"==$lastStr){
                                        $isEnd=true;
                                    }else {
                                        $isEnd=false;
                                    }
                                }
                                $cli->send($this->packer->pack(new HttpResponse($httpResult,$rst->requestFd,$isEnd)));
                                if($isEnd){
                                    $client->close();
                                    break;
                                }
                            }
                        }
                        break;
                }
            }
        });
        $client->on("error", function($cli){
            exit("error\n");
        });
        $client->on("close", function( $client){
            echo "Connection close\n";
            $this->state=self::STATGE_STOPED;
            die;
        });
        $client->set($this->getServiceConfFromPack($this->packConfig));
        if (! $rst=$client->connect($this->tcpConfig->ip, $this->tcpConfig->port, $this->tcpConfig->timeout))
        {
            die("connect failed.");
        }
        //$client->close();
    }


    /**
     * @param Msg|String $sendData
     * @author Jiankang maijiankang@foxmail.com
     */
    private function sendToTcpServer($sendData){
        echo 'send:'.$sendData.PHP_EOL;
        $this->client->send($this->packer->pack($sendData));
    }
    
    
}