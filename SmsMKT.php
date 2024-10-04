<?php

namespace namwansoft\SmsGateway;

class SmsMKT extends \yii\base\Component
{
    private $Url = 'https://portal-otp.smsmkt.com/api';
    private $ApiKey;
    private $ApiSecret;
    private $SenderName;
    private $Proxy;

    public function __construct($ar = null)
    {
        $this->SenderName = $ar->sender_name;
        $this->ApiKey = $ar->key_api;
        $this->ApiSecret = $ar->key_secret;
    }

    /**
     * $host = ip:port
     * $auth = user:pass
     */
    public function setProxy($host = false, $auth = false)
    {
        $this->Proxy = (!$host || !$auth) ? false : (object) ['host' => $host, 'auth' => $auth];
    }

    public function Credit($force = null)
    {
        $cUrl = $this->cUrl('GET', $this->Url . '/get-credit');
        $cUrl['balance'] = $cUrl['result']['credit'];
        return $cUrl;
    }

    /**
     * message
     * sender = ชื่อผู้ส่ง
     */
    public function Send($msisdn, $Body)
    {
        $Body['phone'] = $msisdn;
        $Body['sender'] = $Body['sender'] ?? $this->SenderName;
        return $this->cUrl('POST', $this->Url . '/send-message', null, $Body);
    }

    /**
     * project_key
     * ref_code
     */
    public function OTP($msisdn, $Body)
    {
        $Body['phone'] = $msisdn;
        return $this->cUrl('POST', $this->UrlOtp . '/otp-send', null, $Body);
    }

    /**
     * ref_code
     */
    public function OTPVerify($token, $pin, $Body)
    {
        $Body['token'] = $token;
        $Body['otp_code'] = $pin;
        return $this->cUrl('POST', $this->UrlOtp . '/otp-validate', null, $Body);
    }

    private function cUrl($Method, $Url, $Header = [], $Body = [], $isJson = true)
    {
        $Header = array_merge([
            'Accept:application/json',
            'Content-Type:application/json',
            "api_key:" . $this->ApiKey,
            "secret_key:" . $this->ApiSecret,
        ], $Header ?? []);
        $Body = json_encode($Body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $Method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Body);
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
        $res['message'] = $res['error']['description'] ? $res['error']['description'] : $res['message'];
        return $res;
    }
}
