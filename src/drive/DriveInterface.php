<?php
namespace yii\sms\drive;

interface DriveInterface{
    /**
     * 发送短信
     * @param arr|string $mobile 电话号码
     * @param string $content 短信内容
     * @return boolean
     */
    public function send($mobile,$content);
}