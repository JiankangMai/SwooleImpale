<?php
namespace BromineMai\SwooleImpale\Msg;
class HttpRequest extends Msg {
    public $type=self::MSG_TYPE_HTTP_REQUEST;
    public $domain;
    public $httpData;
    public $requestFd;

    public function __construct($domain=null,$httpData=null,$requestFd=null)
    {
        $this->domain=$domain;
        $this->httpData=$httpData;
        $this->requestFd=$requestFd;
    }


}