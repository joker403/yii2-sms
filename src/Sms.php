<?php

namespace yii\sms;

use Yii;
use yii\base\Component;
use yii\base\Exception;


class Sms extends Component{
    public $drive;
    public $template = []; //消息模板配置
//    public $defaultCodeClass = 'yii\sms\template\Code'; //默认的验证码模板
    public $defaultTempalteClass =  'yii\sms\template\BasicTemp'; //默认的内容模板

    public $_drive;

    public $error;

    public function getError()
    {
        return $this->error;
    }

    public function init()
    {
        parent::init();

        if (empty($this->drive['class'])) {
            throw new Exception("drive 必须设置class");
        }

        $this->_drive = Yii::createObject($this->drive);
        if (!$this->_drive instanceof SmsInterface) {
            throw new Exception("drive 类必须设置并继承 DriveInterface");
        }
    }


    /**
     * 发送消息
     * @param $mobile  手机号
     * @param $content 内容
     * @return bool
     */
    public function sendMsg($mobile, $content = '')
    {
        return $this->_drive->send($mobile, $content);
    }

    /**
     * 发送模板消息 （模板可以再config里配置）
     * @param $mobile
     * @param string $template
     */
    public function sendTempMsg($mobile,$template){
        //获取要使用的模板
        if (!isset($this->template[$template])) {
            throw new Exception("短信模板template未配置该模板");
        }
        //未配置模板的话，使用默认模板
        if(!isset($this->template[$template]['class'])){
            $this->template[$template]['class'] = $this->defaultTempalteClass;
        }
        $config = $this->template[$template];

        //创建模板对象
        $config['mobile'] = $mobile;
        $tempObj = Yii::createObject($config);
        if (!$tempObj instanceof Template) {
            throw new Exception("模板需要继承Template");
        }



        //发送短信
        $result = $this->sendMessage($mobile,$tempObj->getContent());

        return $result;
    }
}
