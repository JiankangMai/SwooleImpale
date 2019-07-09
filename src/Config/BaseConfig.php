<?php
namespace BromineMai\SwooleImpale\Config;
use BromineMai\SwooleImpale\Msg\DataLoadTrait;

class BaseConfig {
    use DataLoadTrait;
    public function __construct(array $config)
    {
        $this->loadData($config);
    }

}