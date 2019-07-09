<?php
namespace BromineMai\SwooleImpale\Server;
use BromineMai\SwooleImpale\Config\DomainTableConfig;
use BromineMai\SwooleImpale\Config\PackConfig;
use BromineMai\SwooleImpale\Config\WebListenConfig;
use BromineMai\SwooleImpale\Msg\DataLoadTrait;
use BromineMai\SwooleImpale\Config\TcpListenConfig;
use BromineMai\SwooleImpale\Msg\Msg;
use BromineMai\SwooleImpale\Msg\Register;
use BromineMai\SwooleImpale\Msg\RegisterRst;
use BromineMai\SwooleImpale\Util\Packer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;

class Server{
    
    /** @var  PackConfig $packConfig */
    public $packConfig;
    /** @var  DomainTableConfig $domainTableConfig */
    public $domainTableConfig;
    /**
     * @var Table
     */
    public $domainTable=null;
    /**
     * @var WebListenConfig $webListenConfig
     */
    public $webListenConfig=null;
    
    use DataLoadTrait;
    /** @var TcpListenConfig $tcpConfig */
    private $tcpConfig;
    
    
    public function init(array $config){
        $this->loadData($config);
        
        $table = new \Swoole\Table($this->domainTableConfig->domainCount);
        $table->column('fd', \Swoole\Table::TYPE_INT, $this->domainTableConfig->fdLong);
        $table->column('domain', \Swoole\Table::TYPE_STRING, $this->domainTableConfig->domainLong);
        $table->create();
        $this->domainTable=$table;
        return $this;
    }
    
    public function start(){

        $httpServer = new \Swoole\Http\Server($this->webListenConfig->ip, $this->webListenConfig->port);
        /**
         * http代理服务器
         */
        $httpServer->on('request', function ($request, $response) {
            $response->header("Content-Type", "text/html; charset=utf-8");
            $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
        });
        /**
         * 注册服务器
         */
        $tcpServer=$httpServer->addListener($this->tcpConfig->ip,$this->tcpConfig->port,SWOOLE_SOCK_TCP);
        //$tcpServer = new \Swoole\Server($this->tcpConfig->ip, $this->tcpConfig->port);
        $tcpServer->on('connect', function ($serv, $fd) {
            echo "Client: Connect.\n";
        });
        $tcpServer->on('receive', [$this,'onReceive']);
        $tcpServer->on('close', [$this,'onClose']);
        $tcpServer->set(array(
            'open_length_check'     => $this->packConfig->openLengthCheck,
            'package_length_type'   => $this->packConfig->packageLengthType,
            'package_length_offset' => $this->packConfig->packageLengthOffset,  
            'package_body_offset'   => $this->packConfig->packageBodyOffset,  
            'package_max_length'    => $this->packConfig->packageMaxLength,  
        ));
        $httpServer->start();
    }

    public function onReceive(\Swoole\Server $serv, int $fd, int $reactor_id, string $data){
        $packer=Packer::getInstance();
        $data=$packer->unPack($data);
        echo 'onRecv:'.$data.PHP_EOL;
        $objMsg=Msg::fromString($data);
        switch ($objMsg->type){
            case Msg::MSG_TYPE_REGISTER:
                /**  @var Register $objMsg */
                $regRst=$this->doRegist($objMsg,$fd);
                $serv->send($fd, $packer->pack($regRst));
                break;
            default:
                echo 'error data:'.(string)$objMsg.PHP_EOL;
        }
    }

    public function onClose(\Swoole\Server $serv, $fd){
        echo "Client {$fd}: Close.\n";
        foreach($this->domainTable as $k=>$row)
        {
            if($fd==$row['fd']){
                $domain=$row['domain'];
                $this->domainTable->del($k);
                echo 'unbind domain:'.$domain.PHP_EOL;
            }
        }
    }
    
    /**
     * 注册域名
     * @param Register $reg
     * @param int $fd
     * @return RegisterRst
     * @author Jiankang maijiankang@foxmail.com
     */
    public function doRegist(Register $reg,int $fd){
        
        if(empty($reg->domain) ){
            return new RegisterRst(RegisterRst::RST_TYPE_FAIL,$reg->domain);
        }else{
            $bindRecord=$this->domainTable->get($reg->domain);
            if(!empty($bindRecord)){
                return new RegisterRst(RegisterRst::RST_TYPE_DOMAIN_USED,$reg->domain);
            }else{
                $this->domainTable->set($reg->domain,['domain'=>$reg->domain,'fd'=>$fd]);
                return new RegisterRst(RegisterRst::RST_TYPE_SUCCESS,$reg->domain);
            }  
        } 
    }
    
}