<?php
namespace BromineMai\SwooleImpale\Server;
use BromineMai\SwooleImpale\Config\DomainTableConfig;
use BromineMai\SwooleImpale\Config\PackConfig;
use BromineMai\SwooleImpale\Config\WebListenConfig;
use BromineMai\SwooleImpale\Msg\DataLoadTrait;
use BromineMai\SwooleImpale\Config\TcpListenConfig;
use BromineMai\SwooleImpale\Msg\HttpRequest;
use BromineMai\SwooleImpale\Msg\HttpResponse;
use BromineMai\SwooleImpale\Msg\Msg;
use BromineMai\SwooleImpale\Msg\Register;
use BromineMai\SwooleImpale\Msg\RegisterRst;
use BromineMai\SwooleImpale\Util\Packer;
use BromineMai\SwooleImpale\Util\Pipe;
use Swoole\Table;

class Server {
    use DataLoadTrait;
    use Service;
    
    
    /** @var  PackConfig $packConfig */
    public $packConfig;
    /** @var  DomainTableConfig $domainTableConfig */
    public $domainTableConfig;
    /**
     * @var Table
     */
    public $domainTable=null;
    /**
     * @var Table
     */
    public $fdTable=null;
    /**
     * @var WebListenConfig $webListenConfig
     */
    public $webListenConfig=null;
    
    
    /** @var TcpListenConfig $tcpConfig */
    private $tcpConfig;
    /**
     * @var \Swoole\Http\Server $httpServer
     */
    private $httpServer=null;
    /**
     * @var \swoole_server_port $tcpServer
     */
    private $tcpServer=null;
    /**
     * @var \Swoole\Http\Response[] $resMap
     */
    private $resMap=[];
    /**
     * @var int $pid
     */
    private $pid;

    public function init(array $config){
        $this->loadData($config);
        $this->pid=posix_getpid();
        $table = new \Swoole\Table($this->domainTableConfig->domainCount);
        $table->column('fd', \Swoole\Table::TYPE_INT, $this->domainTableConfig->fdLong);
        $table->column('domain', \Swoole\Table::TYPE_STRING, $this->domainTableConfig->domainLong);
        $table->create();
        $this->domainTable=$table;
        return $this;
    }
    
    public function start(){

        $this->httpServer =$httpServer= new \Swoole\Http\Server($this->webListenConfig->ip, $this->webListenConfig->port);
        /**
         * http代理服务器
         */
        $httpServer->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response){
            $domain=current(explode(':',$request->header['host']));
            $fd=$this->domainTable->get($domain,'fd');
            if(empty($fd)){
                $response->header("Content-Type", "text/html; charset=utf-8");
                $response->status(404);
                $response->end("<h1>Unknow Domain:{$domain}</h1>");
            }else{
                $this->resMap[$request->fd]=$response;
                $packer=Packer::getInstance();
                $this->httpServer->send($fd, $packer->pack(new HttpRequest($domain,$request->getData(),$request->fd)));
                $pipe=new Pipe($this->pid,$packer);
                $pipe->initPipe($request->fd);
                while(1){
                    $rst=$pipe->readFromPipe($request->fd);
                    if(false===$rst){
                        usleep(1000*300);
                    }else{
                        $objMsg=Msg::fromString($rst);
                        /** @var HttpResponse $objMsg */
                        $this->httpServer->send($response->fd,$objMsg->result);
                        if($objMsg->isEnd){
                            break;
                        }
                        
                    }
                };
                $pipe->releasePipe($request->fd);
                $this->httpServer->close($response->fd); 
            }
            echo 'end for:'.$response->fd.PHP_EOL;
            
        });
        /**
         * 注册服务器
         */
        $this->tcpServer=$tcpServer=$httpServer->addListener($this->tcpConfig->ip,$this->tcpConfig->port,SWOOLE_SOCK_TCP);
        $tcpServer->on('connect', function ($serv, $fd) {
            echo "Client: Connect.\n";
        });
        $tcpServer->on('receive', [$this,'onReceive']);
        $tcpServer->on('close', [$this,'onClose']);
        $tcpServer->set($this->getServiceConfFromPack($this->packConfig));
        $httpServer->start();
    }

    public function onReceive(\Swoole\Server $serv, int $fd, int $reactor_id, string $data){
        $packer=Packer::getInstance();
        $data=$packer->unPack($data);
        $objMsg=Msg::fromString($data);
        switch ($objMsg->type){
            case Msg::MSG_TYPE_REGISTER:
                /**  @var Register $objMsg */
                $regRst=$this->doRegist($objMsg,$fd);
                $serv->send($fd, $packer->pack($regRst));
                break;
            case Msg::MSG_TYPE_HTTP_RESPOENSE:
                /**  @var HttpResponse $objMsg */
                $pipe=new Pipe($this->pid,$packer);
                $pipe->sendToPipe($objMsg ->requestFd,$data);
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