<?php
namespace BromineMai\SwooleImpale\Msg;
class RegisterRst extends Msg {
    public $type=self::MSG_TYPE_REGISTER_RESULT;
    const RST_TYPE_SUCCESS=1;
    const RST_TYPE_FAIL=2;
    const RST_TYPE_DOMAIN_USED=3;
    public $rstType;
    public $domain;
    public function __construct($rstType=null,$domain=null)
    {
        $this->rstType=$rstType;
        $this->domain=$domain;
    }
    
    
    public function getRstTypeStr($rstType){
        return [
            self::RST_TYPE_SUCCESS=>'注册成功',
            self::RST_TYPE_FAIL=>'服务器注册失败',
            self::RST_TYPE_DOMAIN_USED=>'域名已注册',
        ][$rstType]??null;
    }


}