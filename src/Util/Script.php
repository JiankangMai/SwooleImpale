<?php
namespace BromineMai\SwooleImpale\Util;

class Script{
    /**
     * @param array $allowParam  参数列表   支持传递方式： 全部不提供默认值 ['uid']    部分提供默认值  ['uid'=>1,'age'=>null]
     * @return array 
     * @throws \Exception
     * @author Jiankang maijiankang@foxmail.com
     */
    public static function parseCliParam($allowParam){
        global $argv;
        $input=$argv;
        unset($input[0]);
        $return=[];
        $haveDefaultValue=(bool)array_diff_assoc(array_keys($allowParam), range(0, sizeof($allowParam)));
        $allowParamNameArr=$haveDefaultValue?array_keys($allowParam):array_values($allowParam);

        foreach ($input as $inputItemStr){
            $exRst=explode('=',$inputItemStr,2);
            if(2!=count($exRst) || strlen($exRst[0])<=2){
                throw new \Exception('error param format to script',['argv'=>var_export($argv,true),'paramNameArr'=>var_export($allowParamNameArr,true)]);
            }else{
                $inputName=substr($exRst[0],2);
                if(!in_array($inputName,$allowParamNameArr)){
                    throw new \Exception('error param to script,please check',['argv'=>var_export($argv,true),'paramNameArr'=>var_export($allowParamNameArr,true)]);
                }
                $return[$inputName]=$exRst[1];
            }
        }
        foreach ($allowParamNameArr as $allowParamNameItem){
            if(!isset($return[$allowParamNameItem])){
                $return[$allowParamNameItem]=$haveDefaultValue?$allowParam[$allowParamNameItem]:null;
            }
        }
        return $return ;
    }
}