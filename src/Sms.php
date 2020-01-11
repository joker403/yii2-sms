<?php

namespace yii\sms;

use common\components\sms\template\Code;
use Yii;
use yii\base\Component;
use yii\base\Exception;


class Sms extends Component
{
    public $drive;
    public $template = []; //消息模板配置
    public $defaultCodeClass = 'yii\sms\template\Code'; //默认的验证码模板
    public $defaultTempalteClass =  'yii\sms\Template'; //默认的内容模板

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
            throw new Exception("drive 类必须设置并继承 SmsInterface");
        }
    }


    /**
     * @param $mobile
     * @param $content
     * @return bool
     */
    public function sendMessage($mobile, $content = '')
    {
        return $this->_drive->send($mobile, $content);
    }

    /**
     * @param $mobile
     */
    public function sendCode($mobile, $template = '')
    {
        if (empty($template)) {
            $config = ['class' => $this->defaultCodeClass];
        } elseif (!empty($template) && isset($this->template[$template])) {
            if (!isset($this->template[$template]['class'])) {
                $this->template[$template]['class'] = $this->defaultCodeClass;
            }
            $config = $this->template[$template];
        } else {
            throw new Exception("短信模板template配置中未配置该摸坂");
        }

        $config['mobile'] = $mobile;
        $codeObj = Yii::createObject($config);

        if (!$codeObj instanceof Template) {
            throw new Exception("模板需要继承Template");
        }

        if (!$codeObj->beforeSend()) {
            $error = $codeObj->getError();
            $this->error = !empty($error) ? $error : '发送前检测未通过';
            return false;
        }

        $result = $this->sendMessage($mobile, $codeObj->getContent());

        $codeObj->afterSend($result);
        return $result;
    }

    /**
     * @param $mobile
     */
    public function sendCodeNew($mobile, $template = '', $type)
    {
        if (empty($template)) {
            $config = ['class' => $this->defaultCodeClass];
        } elseif (!empty($template) && isset($this->template[$template])) {
            if (!isset($this->template[$template]['class'])) {
                $this->template[$template]['class'] = $this->defaultCodeClass;
            }
            $config = $this->template[$template];
        } else {
            throw new Exception("短信模板template配置中未配置该摸坂");
        }

        $config['mobile'] = $mobile;
        $config['type'] = $type;
        $codeObj = Yii::createObject($config);

        if (!$codeObj instanceof Template) {
            throw new Exception("模板需要继承Template");
        }

        if (!$codeObj->beforeSend()) {
            $error = $codeObj->getError();
            $this->error = !empty($error) ? $error : '发送前检测未通过';
            return false;
        }

        $result = $this->sendMessage($mobile, $codeObj->getContent());

        $codeObj->afterSend($result);
        return $result;
    }

    /**
     * @param $mobile
     * @param $code
     * @param string $template
     * @return mixed
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function checkCode($mobile, $code, $template = '')
    {
        if (empty($template)) {
            $config = ['class' => $this->defaultCodeClass];
        } elseif (!empty($template) && isset($this->template[$template])) {
            if (!isset($this->template[$template]['class'])) {
                $this->template[$template]['class'] = $this->defaultCodeClass;
            }
            $config = $this->template[$template];
        } else {
            throw new Exception("短信模板template配置中未配置该摸坂");
        }

        $config['mobile'] = $mobile;
        $codeObj = Yii::createObject($config);

        if (!$codeObj instanceof Template) {
            throw new Exception("模板需要继承Template");
        }

        return $codeObj->checkCode($code);
    }
}
