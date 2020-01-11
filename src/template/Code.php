<?php
namespace yii\sms\template;

use yii\sms\Template;

class Code extends Template
{
    public $body = "您的短信验证码为{code},有效期{min}分钟";

    public $mobile;

    public $type;

    public $authTime = 5; //5分钟内均可验证

    public $waitTime = 60; //60秒内不能重复（请和前端保持一致，或小于前端设置等待时间）

    public $ipDayNum = 60;

    public $codeLength = 4;

    public $code;

    public function init()
    {
        parent::init();

        $this->param['{code}'] = $this->generateVerifyCode();
        $this->param['{min}'] = $this->authTime;
    }

    /**
     * 生成一个验证码
     * @return string the generated verification code
     */
    protected function generateVerifyCode()
    {
        $length = $this->codeLength;
        $min = pow(10, ($length - 1));
        $max = pow(10, $length) - 1;
        $this->code = rand($min, $max);
        return $this->code;
    }

    /**
     * 对发送进行验证,防止频繁发送
     * @return bool
     */
    public function beforeSend()
    {
        $cache = \Yii::$app->cache;
        // $key = $this->getCacheKey($this->mobile);
        $key = $this->getCacheKey($this->mobile . $this->type);

        $result = $cache->get($key);

        //判断是否超过每日数量
        if ($result && $result && $result['ipDayNum'] >= $this->ipDayNum) {
            $this->error = "您的IP超过每日发送限制";
            return false;
        }

        //判断是否超过每日数量
        if ($result && $result['ipDayNum'] >= $this->ipDayNum) {
            $this->error = "您的IP超过每日发送限制";
            return false;
        }

        //判断是否过于频繁
        if ($result && $result['sendTime'] > time() - $this->waitTime) {
            $this->error = "您的验证发送过于频繁,请间隔{$this->waitTime}秒";
            return false;
        }

        return true;
    }

    /**
     * 发送后记录发送结果
     * @param $result 发送的结果
     * @return bool
     */
    public function afterSend($result)
    {
        if (!$result) {
            return false;
        }

        $cache = \Yii::$app->cache;
        // $key = $this->getCacheKey($this->mobile);
        $key = $this->getCacheKey($this->mobile . $this->type);

        //缓存数据
        $data['mobile'] = $this->mobile;
        $data['code'] = $this->code;
        $data['sendTime'] = time();
        $data['ipDayNum'] = 1;

        if ($result = $cache->get($key)) {
            //如果是同一天发送的，则记录ipNum
            if (date('Y-m-d', $data['sendTime']) == date('Y-m-d', $result['sendTime'])) {
                $data['ipDayNum'] = $result['ipDayNum'] + 1;
            }
        }

        $tomorrow = strtotime(date("Y-m-d", strtotime("+1 day")));
        $cache->set($key, $data, $tomorrow - time()); //缓存1天

        return true;
    }

    /**
     * Returns the session variable name used to store verification code.
     * 返回用于存储验证代码的会话变量名。
     * @param string $mobile
     * @return string the session variable name
     */
    protected function getCacheKey($mobile)
    {
        return '__checkmobile/' . $_SERVER['REMOTE_ADDR'] . '/' . $mobile;
    }

    public function checkCode($code)
    {
        $cache = \Yii::$app->cache;
        $key = $this->getCacheKey($this->mobile);

        $result = $cache->get($key);

        if (!$result) {
            return -2;
        }

        if ($result['code'] != $code) {
            return -2;
        }

        $time = time() - $this->authTime * 60;
        if ($result['sendTime'] < $time) {
            return -1;
        }

        return true;
    }
}
