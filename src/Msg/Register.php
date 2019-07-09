<?php
namespace BromineMai\SwooleImpale\Msg;
class Register extends Msg {
    public $type=self::MSG_TYPE_REGISTER;
    public $domain;

    public function __construct($domain=null)
    {
        $this->domain=$domain;
    }


}