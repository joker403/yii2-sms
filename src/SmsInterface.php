<?php
/**
 * sms 短信接口
 * User: cxf
 * Date: 2017/11/3
 * Time: 21:47
 */

namespace yii\sms;

interface SmsInterface
{
    /**
     * 发送短信
     * @param arr|string $mobile 电话号码
     * @param string $content 短信内容
     * @return boolean
     */
    public function send($mobile,$content);
}