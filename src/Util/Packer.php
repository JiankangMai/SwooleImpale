<?php
namespace BromineMai\SwooleImpale\Util;
use BromineMai\SwooleImpale\Config\PackConfig;

/**
 * 数据封包 解包
 * Class Pack
 * @package BromineMai\SwooleImpale\Util
 */
class Packer{
    
    private static $instanceArr=[];
    /** @var  PackConfig */
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
                $obj=new self();
                if(empty($config['packConfig']) || !$config['packConfig'] instanceof PackConfig ){
                    throw new \Exception('packConfig配置缺失');
                }
                $obj->packConfig=$config['packConfig'];
                self::$instanceArr[$packerName]=$obj;
            }
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
        return  pack($this->packConfig->packageLengthType, strlen($strData)) . $strData;
    }

    /**
     * 网络数据解包
     * @param $strData
     * @return bool|string
     * @author Jiankang maijiankang@foxmail.com
     */
    public function unPack($strData){
        $info = unpack($this->packConfig->packageLengthType, $strData);
        $len = $info[1];
        return substr($strData, - $len);
    }
    
}