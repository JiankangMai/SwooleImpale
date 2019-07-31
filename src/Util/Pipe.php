<?php
namespace BromineMai\SwooleImpale\Util;


/**
 * Class Pipe
 * @package BromineMai\SwooleImpale\Util
 */
class Pipe{
    
    private $appId; 
    private $packer;
    
    
    
    public function __construct($appId,Packer $packer)
    {
        $this->packer=$packer;
        $this->appId=$appId;
    }
    
    private function getPipePath($pipeId){
        return PIPE_DIR.$this->appId.'_'.$pipeId;
    }

    /**
     * 初始化管道
     * @param $pipeId
     * @author Jiankang maijiankang@foxmail.com
     */
    public function initPipe($pipeId){
        umask(0);
        $pipePath = $this->getPipePath($pipeId);
        posix_mkfifo ( $pipePath, 0600);
    }

    /**
     * 删除管道
     * @param $pipeId
     * @author Jiankang maijiankang@foxmail.com
     */
    public function releasePipe($pipeId){
        $pipePath = $this->getPipePath($pipeId);
        unlink($pipePath);
    }

    /**
     * 阻塞读
     * @param $pipeId
     * @return bool|string
     * @author Jiankang maijiankang@foxmail.com
     */
    public function readFromPipe($pipeId){
        $packer=$this->packer;;
        $pipePath=$this->getPipePath($pipeId);
        $fp = fopen($pipePath, 'rb');
        $result = fread($fp,$packer->getHeadLen());
        $result=$packer->getDataLenght($result );
        $result = fread($fp,$result);
        return $result ;
    }

    /**
     * 阻塞写
     * @param $pipeId
     * @param $data
     * @author Jiankang maijiankang@foxmail.com
     */
    public function sendToPipe($pipeId, $data){
        $pipePath = $this->getPipePath($pipeId);
        $fp = fopen($pipePath, 'wb');
        $packer=$this->packer;
        $data=$packer->pack($data);
        @fwrite($fp,$data);
    }

}