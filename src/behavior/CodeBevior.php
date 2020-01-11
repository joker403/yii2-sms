<?php
namespace yii\sms\behavior;

use yii\base\Behavior;

class CodeBehavior extends Behavior
{
    /**
     * 发送验证短信
     * @param $mobile  手机号
     * @param string $template  消息模板
     * @param string $prefix  前缀（如果有多个地方需要短信验证，可以在此区分）
     */
    public function sendCode($mobile,$template="",$prefix="sms-code"){
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
     * 验证短信验证码
     * @param $mobile  手机号
     * @param $code  验证码
     * @param string $prefix   前缀（如果有多个地方需要短信验证，可以在此区分）
     */
    public function checkCode($mobile,$code,$prefix="sms-code"){
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