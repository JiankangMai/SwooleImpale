<?php
namespace BromineMai\SwooleImpale\Util;
use BromineMai\SwooleImpale\Config\EofPackConfig;
use BromineMai\SwooleImpale\Config\LenghtPackConfig;
use BromineMai\SwooleImpale\Config\PackConfig;

/**
 * 数据封包 解包
 * Class Pack
 * @package BromineMai\SwooleImpale\Util
 */
class Packer{
    
    private static $instanceArr=[];
    /** @var  LenghtPackConfig */
    private $packConfig;

    /**
     * @param string $packerName 用于唯一表示一个packer
     * @param PackConfig $config
     * @return self
     * @throws \Exception
     * @author Jiankang maijiankang@foxmail.com
     */
    public static function getInstance($packerName='default', PackConfig $config=null){
        if(!isset(self::$instanceArr[$packerName])){
            if(empty($config)){
                $config=require CONF_DIR.'pack.php';
                $config=$config['packConfig']??null;
            }
            $obj=new self();
            if(empty($config) || !$config instanceof PackConfig ){
                throw new \Exception('packConfig配置缺失');
            }
            $obj->packConfig=$config;
            self::$instanceArr[$packerName]=$obj;
        }
        return self::$instanceArr[$packerName];
    }

    /**
     * 封包用于网络传输
     * @param $strData
     * @return string
     * @author Jiankang maijiankang@foxmail.com
     */
    public function pack($strData){
        
        if(!is_string($strData)){
            $strData=(string)$strData;
        }
        $packConfig=$this->packConfig;
        switch (get_class($packConfig)){
            case LenghtPackConfig::class:
                /** @var  LenghtPackConfig $packConfig */
                return  pack($packConfig->packageLengthType, strlen($strData)) . $strData;
            case EofPackConfig::class:
                /** @var  EofPackConfig $packConfig */
                return $strData.$packConfig->packageEof;
            default:
                return $strData; 
        }
    }

    /**
     * 网络数据解包
     * @param $strData
     * @return bool|string
     * @author Jiankang maijiankang@foxmail.com
     */
    public function unPack($strData){
        $packConfig=$this->packConfig;
        switch (get_class($packConfig)){
            case LenghtPackConfig::class:
                /** @var  LenghtPackConfig $packConfig */
                $info = unpack($packConfig->packageLengthType, $strData);
                $len = $info[1];
                return substr($strData, - $len);
            case EofPackConfig::class:
                /** @var  EofPackConfig $packConfig */
                $len=strlen($strData)-strlen($packConfig->packageEof);
                return substr($strData, 0,$len);
            default:
                return $strData;
        }
    }
    
    
    public function getHeadLen(){
        return $this->packConfig->packageBodyOffset;
    }
    
    public function getDataLenght($strHead){
        $info = unpack($this->packConfig->packageLengthType, $strHead);
        return  $info[1];
    }
    
}