<?php
namespace BromineMai\SwooleImpale\Msg;
class HttpRequest extends Msg {
    public $type=self::MSG_TYPE_REGISTER;
    public $domain;
    public $httpData;

    public function __construct($domain=null,$httpData=null)
    {
        $this->domain=$domain;
        $this->httpData=$httpData;
    }


}