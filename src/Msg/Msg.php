<?php
namespace BromineMai\SwooleImpale\Msg;

class Msg{
    use DataLoadTrait{
        //loadJson as public traitLoadJson;
    }
    CONST MSG_UNKONW='unknow';//不知道
    CONST MSG_TYPE_REGISTER='reg';//注册
    CONST MSG_TYPE_REGISTER_RESULT='regrst';//注册结果
    CONST MSG_TYPE_HTTP_REQUEST='httpReq';//http请求
    CONST MSG_TYPE_HTTP_RESPOENSE='httpRes';//http结果
    
    
    const TYPE_CLASS_MAP=[
        self::MSG_UNKONW=>Msg::class,
        self::MSG_TYPE_REGISTER=>Register::class,
        self::MSG_TYPE_REGISTER_RESULT=>RegisterRst::class,
        self::MSG_TYPE_HTTP_REQUEST=>HttpRequest::class,
    ];


    public $type;
    public $ver=1.0;
    
    /**
     * 反序列化
     * @param string $strObj
     * @return Msg
     * @see Msg::__toString()
     * @author Jiankang maijiankang@foxmail.com
     */
    public static function fromString(string $strObj){
        $data =json_decode($strObj,true);
        $className=self::TYPE_CLASS_MAP[$data['type']]??Msg::class;
        /** @var  Msg $obj */
        $obj=new $className;
        $obj->loadData($data);
        return $obj;
    }


    /**
     * @return string
     * @author Jiankang maijiankang@foxmail.com
     */
    function __toString()
    {
        return json_encode($this,JSON_UNESCAPED_UNICODE);
    }


}