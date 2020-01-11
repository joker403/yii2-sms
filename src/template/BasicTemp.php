<?php
/**
 * 模板：基础消息模板
 * @author:      cxf
 * @version：    1.0
 * @date:        2020/1/11 18:50
 */

namespace yii\sms\template;


use yii\base\Behavior;
use yii\helpers\ArrayHelper;

class BasicTemp extends Behavior
{
    public $head = "【XXX系统】";

    public $body = "hello world";

    public $param = [];

    protected $error = "";

    public function getError(){
        return $this->error;
    }

    public function getContent($param = []){
        if(!empty($param)){
            $this->param = ArrayHelper::merge($this->param,$param);
        }
        return $this->head.strtr($this->body,$this->param);
    }
}