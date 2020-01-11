<?php
/**
 * Created by PhpStorm.
 * User: cxf
 * Date: 2018/9/30
 * Time: 3:13
 */

namespace yii\sms;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class Template extends BaseObject
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

    public function beforeSend(){
        return true;
    }

    /**
     * @param $result 发送的结果
     * @return bool
     */
    public function afterSend($result){
        return true;
    }
}