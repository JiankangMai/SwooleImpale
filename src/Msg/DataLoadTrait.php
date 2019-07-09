<?php
namespace BromineMai\SwooleImpale\Msg;
Trait DataLoadTrait{
    public function loadData(array $data)
    {
        foreach ($data as $k=> $v){
            $this->$k=$v;
        }
        return $this; 
    }

    public function loadJson(string $json)
    {
        $data =json_decode($json,true);
        foreach ($data as $k=>$v){
            $this->$k=$v;
        }
        return $this;
    }

}