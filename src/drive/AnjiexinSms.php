<?php
/**
 * 安捷信短信接口
 * User: cxf
 * Date: 2017/11/3
 * Time: 21:43
 */
namespace yii\sms\drive;

use yii\sms\SmsInterface;
use yii\base\Exception;

class AnjiexinSms implements SmsInterface {
    public $url = "http://124.251.7.232:9007/axj_http_server/sms";

    public $username;

    public $password;

    private $sendId;//发送批号

    public $msgtpl = "【XX公司】您的手机验证码为{code},{time}分钟内有效!";

    public $authTime = 5;//5分钟内均可验证

    public $waitTime = 60;//60秒内不能重复（请和前端保持一致，或小于前端设置等待时间）

    public $ipDayNum = 60;

    public $codeLength = 4;

    public $code = [
        '00' => '提交成功',
        '01' => '提交参数异常',
        '02' => '手机号参数异常',
        '03' => '扩展号参数异常',
        '04' => '发送时间参数异常',
        '05' => '短信内容解析异常',
        '15' => '短信余量不足',
    ];

    public function send($mobile,$content)
    {
        $param['mobiles'] = $mobile;
        $param['content'] = $content;

        return $this->httpCurl($this->url,$param);
    }

    private function httpCurl($url,$param,$method="get",$timeout=10){
        try{
            $param['name'] = $this->username;
            $param['pass'] = $this->password;

            if(strtolower($method) == 'get'){
                $url = $url.'?'.http_build_query($param);
            }

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_HEADER,0);
            curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);

            if(strtolower($method) == 'post'){
                curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
            }

            $result = curl_exec($ch);

            //获取返回状态 200为正常
            $http_status = curl_getinfo($ch);
            if(isset($http_status) && $http_status['http_code']!="200"){
                throw new Exception("http访问错误：".$http_status['http_code']);
            }

            if(!$result){
                throw new Exception("接口返回为空");
            }

            list($send_id,$status) = explode(',',trim($result));

            if($status != "00"){
                $msg = isset($this->code[$status])?$this->code[$status]:'未知错误';
                throw new Exception("接口返回错误:".$msg);
            }

            return true;
        }catch(\Exception $e) {
            $this->erros[] = $e->getMessage();
            return false;
        }
    }


//    public function curlHttp($url)
//    {
//        try {
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_HEADER, 0);
//
//            $result = curl_exec($ch);
//            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//            if ($http_code !== 200) {
//                throw new Exception("api url code:" . $http_code);
//            }
//
//            curl_close($ch);
//            return $result;
//        } catch (Exception $e) {
////            Yii::trace('sms curl 出现错误:' . $e->getMessage());
////            $this->message = $e->getMessage();
//        }
//    }


//
//    /**
//     * verify sms code
//     * @param string $code
//     * @param string $mobile
//     * @return array
//     */
//    public function verify ($code, $mobile)
//    {
//        $cache = \Yii::$app->cache;
//        $key = $this->getCacheKey($mobile);
//
//        $result = $cache->get($key);
//
//        if ($code != $result['code']) {
//
//            $this->message = '验证码错误';
//            return ['status' => 0, 'msg' => $this->message];
//        } else {
//
//            if (time() > $result['sendTime'] + $this->authTime * 60) {
//                $this->message = '验证码已失效';
//                return ['status' => 0, 'msg' => $this->message];
//            } else {
//                $this->message = '验证成功';
//                return ['status' => 1, 'msg' => $this->message];
//            }
//        }
//
//    }
//
//    /**
//     * Returns the session variable name used to store verification code.
//     * 返回用于存储验证代码的会话变量名。
//     * @param string $mobile
//     * @return string the session variable name
//     */
//    protected function getCacheKey($mobile)
//    {
//        return '__checkmobile/'.$_SERVER['REMOTE_ADDR'].'/' . $mobile;
//    }
//
//    private function beforeSend ($mobile)
//    {
//
//        $cache = \Yii::$app->cache;
//        $key = $this->getCacheKey($mobile);
//
//        $result = $cache->get($key);
//
//        //第一次发送，没有限制
//        if(!$result){
//            return ['status' => 1, 'msg' =>''];
//        } else {
//            //判断是否超过每日数量
//            if($result['ipDayNum'] >= $this->ipDayNum){
//                $this->message = "您的IP超过每日发送限制";
//                return ['status' => 0, 'msg' => $this->message];
//            }
//
//            //判断是否过于频繁
//            if($result['sendTime'] > time()-$this->waitTime){
//                $this->message = "您的验证发送过于频繁,请稍后";
//                return ['status' => 0, 'msg' => $this->message];
//            }
//
//            return ['status' => 1, 'msg' =>''];
//        }
//    }
//
//    /**
//     * 生成一个验证码
//     * @return string the generated verification code
//     */
//    protected function generateVerifyCode()
//    {
//        $length = $this->codeLength;
//        $min = pow(10 , ($length - 1));
//        $max = pow(10, $length) - 1;
//        return rand($min, $max);
//    }
//
//    private function afterSend($mobile, $code){
//        $cache = \Yii::$app->cache;
//        $key = $this->getCacheKey($mobile);
//
//        //缓存数据
//        $data['mobile'] = $mobile;
//        $data['code'] = $code;
//        $data['sendTime'] = time();
//        $data['ipDayNum'] = 1;
//
//        if($result = $cache->get($key)){
//            //如果是同一天发送的，则记录ipNum
//            if(date('Y-m-d',$data['sendTime']) == date('Y-m-d',$result['sendTime'])){
//                $data['ipDayNum'] = $result['ipDayNum'] + 1;
//            }
//        }
//
//        $tomorrow = strtotime(date("Y-m-d",strtotime("+1 day")));
//
//        $cache->set($key,$data, $tomorrow-time());//缓存1天
//    }
//
//    public function getSendId(){
//        return $this->sendId;
//    }
//
}