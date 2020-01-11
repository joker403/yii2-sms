<?php
/**
 * 安捷信短信接口
 * User: cxf
 * Date: 2017/11/3
 * Time: 21:43
 */
namespace yii\sms\drive;

use yii\sms\drive\DriveInterface;
use yii\base\Exception;

class Anjiexin implements DriveInterface {
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
}