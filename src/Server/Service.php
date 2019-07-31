<?php
namespace BromineMai\SwooleImpale\Server;
use BromineMai\SwooleImpale\Config\DomainTableConfig;
use BromineMai\SwooleImpale\Config\EofPackConfig;
use BromineMai\SwooleImpale\Config\LenghtPackConfig;
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
use Swoole\Client;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;

Trait Service{
    
    public function getServiceConfFromPack(PackConfig $packConfig){
        switch (get_class($packConfig)){
            case LenghtPackConfig::class:
                /*** @var LenghtPackConfig $packConfig */
                return [
                    'open_length_check'     => true,
                    'package_length_type'   => $packConfig->packageLengthType,
                    'package_length_offset' => $packConfig->packageLengthOffset,  
                    'package_body_offset'   => $packConfig->packageBodyOffset,  
                    'package_max_length'    => $packConfig->packageMaxLength,   
                ];
                break;
            case EofPackConfig::class:
                /*** @var EofPackConfig $packConfig */
                return [
                    'open_eof_check' => true,
                    'package_eof' => $packConfig->packageEof,
                    'open_eof_split'=>$packConfig->openEofSplit,
                ];
                break;
            default:
                throw new \Exception('Unknow PackConfig');
        }
    }
    
}