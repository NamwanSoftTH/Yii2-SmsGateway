<?php

namespace namwansoft\SmsGateway;

class Sms2Pro extends \yii\base\Component
{
    private $Url = 'https://portal.sms2pro.com/sms-api';
    private $Authorization;
    private $ApiKey;
    private $SenderName;
    private $Proxy;

    public function __construct($ar = null)
    {
        $this->SenderName = $ar->sender_name;
        $this->ApiKey = $ar->key_api;
        $this->Authorization = 'Bearer ' . $this->ApiKey;
    }

    /**
     * $host = ip:port
     * $auth = user:pass
     */
    public function setProxy($host = false, $auth = false)
    {
        $this->Proxy = (!$host || !$auth) ? false : (object) ['host' => $host, 'auth' => $auth];
    }

    public function Credit()
    {
        $cUrl = $this->cUrl('GET', '/profile/get-balance');
        $cUrl['balance'] = $cUrl['status'] ? $cUrl['data']['balance'] : null;
        return $cUrl;
    }

    /**
     * message
     * sender = ชื่อผู้ส่ง
     */
    public function Send($msisdn, $Body)
    {
        $Body['recipient'] = $msisdn;
        $Body['sender_name'] = $Body['sender'] ?? $this->SenderName;
        $cUrl = $this->cUrl('POST', '/message-sms/send', $Body);
        $cUrl['refId'] = $cUrl['data']['uuid'];
        return $cUrl;
    }

    public function SendCheck($uuid)
    {
        $cUrl = $this->cUrl('GET', '/message-sms/get/' . $uuid);
        return $cUrl;
    }

    public function OTP($msisdn)
    {
        $Body['recipient'] = $msisdn;
        $Body['sender_name'] = 'OTPSMS'; // $Body['sender'] ?? $this->SenderName;
        $Body['digit'] = 6;
        $cUrl = $this->cUrl('POST', '/otp-sms/send', $Body);
        $cUrl['token'] = $cUrl['data']['token'];
        return $cUrl;
    }

    public function OTPVerify($token, $pin)
    {
        $Body['token'] = $token;
        $Body['otp_code'] = $pin;
        $cUrl = $this->cUrl('POST', '/otp-sms/verify', $Body);
        $cUrl['status'] = ($cUrl['status'] && $cUrl['data']['is_valid']) ? true : false;
        $cUrl['message'] = ($cUrl['status'] && !$cUrl['data']['is_valid']) ? $cUrl['data']['message'] : $cUrl['message'];
        return $cUrl;
    }

    public function SmsList()
    {
        $cUrl = $this->cUrl('GET', '/message-sms/list');
        return $cUrl;
    }

    private function cUrl($Method, $Url, $Body = [])
    {
        $Header = ['Content-Type:application/json', 'Authorization:' . $this->Authorization];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_URL, $this->Url . $Url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $Method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($Body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $Header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if ($this->Proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->Proxy->host);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->Proxy->auth);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($res, true);
        $res['status'] = $res['status'] == 'success' ? true : false;
        $res['message'] = $res['system_message'];
        return $res;
    }
}
