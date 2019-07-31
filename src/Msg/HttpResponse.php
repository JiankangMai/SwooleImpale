<?php
namespace BromineMai\SwooleImpale\Msg;
class HttpResponse extends Msg {
    public $type=self::MSG_TYPE_HTTP_RESPOENSE;
    public $result;
    public $requestFd;
    public $isEnd;

    public function __construct($result=null,$requestFd=null,$isEnd=null)
    {
        $this->result=$result;
        $this->requestFd=$requestFd;
        $this->isEnd=$isEnd;
    }


}