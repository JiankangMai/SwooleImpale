<?php
/**
 * 网络传输类  具体含义和默认值见对应的配置类
 */
return [
    'packConfig'=>
        new \BromineMai\SwooleImpale\Config\LenghtPackConfig([
            'packageMaxLength'=>81920,
            'packageLengthType'=>'n',
            'packageLengthOffset'=>0,
            'packageBodyOffset'=>2,
        ]
        //new  \BromineMai\SwooleImpale\Config\EofPackConfig([
        //    'packageEof'=>'#b12(7'
        //],
    )
];