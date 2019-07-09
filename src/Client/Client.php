<?php
namespace BromineMai\SwooleImpale\Client;
use BromineMai\SwooleImpale\Config\HttpServerConfig;
use BromineMai\SwooleImpale\Config\PackConfig;
use BromineMai\SwooleImpale\Config\TcpClientConfig;
use BromineMai\SwooleImpale\Msg\DataLoadTrait;
use BromineMai\SwooleImpale\Msg\Msg;
use BromineMai\SwooleImpale\Msg\Register;
use BromineMai\SwooleImpale\Msg\RegisterRst;
use BromineMai\SwooleImpale\Util\Packer;

class Client{
    use DataLoadTrait;

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
    public function init(array $config){
        $this->loadData($config);
        return $this;
    }

    
    public function start(){
        $client = new \Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
        
        $client->on("connect", function( $client){
            echo "connect\n";
            $this->state=self::STATGE_CONNECT;
            foreach ($this->httpServerList as $httpServer){
                $data=Packer::getInstance()->pack(new Register($httpServer->domain));
                if (!$client->send($data))
                {
                    die("send failed.");
                }
            }
        });
        
        $client->on("receive", function($cli, $data){
            $data=Packer::getInstance()->unPack($data);
            echo "Receive: $data".PHP_EOL;
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
        $client->set(array(
            'open_length_check'     => $this->packConfig->openLengthCheck,
            'package_length_type'   => $this->packConfig->packageLengthType,
            'package_length_offset' => $this->packConfig->packageLengthOffset,
            'package_body_offset'   => $this->packConfig->packageBodyOffset,
            'package_max_length'    => $this->packConfig->packageMaxLength,
        ));
        if (! $rst=$client->connect($this->tcpConfig->ip, $this->tcpConfig->port, $this->tcpConfig->timeout))
        {
            die("connect failed.");
        }
        //$client->close();
    }
}