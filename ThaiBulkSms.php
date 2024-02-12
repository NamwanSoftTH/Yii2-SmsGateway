<?php

namespace namwansoft\SmsGateway;

class ThaiBulkSms extends \yii\base\Component
{
    private $Url = 'https://api-v2.thaibulksms.com';
    private $UrlOtp = 'https://otp.thaibulksms.com/v2/otp';
    private $Authorization;
    private $ApiKey;
    private $ApiSecret;
    private $OtpKey;
    private $OtpSecret;
    private $SenderName;
    private $Proxy = false;

    public function __construct($ar = null)
    {
        $this->SenderName = $ar->sender_name;
        $this->ApiKey = $ar->key_api;
        $this->ApiSecret = $ar->key_secret;
        $this->OtpKey = $ar->key_api2;
        $this->OtpSecret = $ar->key_secret2;
        $this->Authorization = 'Basic ' . base64_encode($this->ApiKey . ':' . $this->ApiSecret);
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
        $Header = ['Authorization:' . $this->Authorization];
        $cUrl = $this->cUrl('GET', $this->Url . '/credit', $Header);
        $error = $cUrl['error'];
        $cUrl = $cUrl['remaining_credit'];
        $cUrl['balance'] = ($force) ? $cUrl[$force] : $cUrl['corporate'];
        $cUrl['error'] = $error;
        return $cUrl;
    }

    /**
     * message
     * sender = ชื่อผู้ส่ง
     * force = standard,corporate
     */
    public function Send($msisdn, $Body)
    {
        $Header = ['Authorization:' . $this->Authorization, 'Content-Type:application/x-www-form-urlencoded'];
        $Body['msisdn'] = $msisdn;
        $Body['sender'] = $Body['sender'] ?? $this->SenderName;
        $Body['force'] = $Body['force'] ?? 'corporate';
        return $this->cUrl('POST', $this->Url . '/sms', $Header, http_build_query($Body), false);
    }

    public function OTP($msisdn)
    {
        $Body['key'] = $this->OtpKey;
        $Body['secret'] = $this->OtpSecret;
        $Body['msisdn'] = $msisdn;
        return $this->cUrl('POST', $this->UrlOtp . '/request', null, $Body);
    }

    public function OTPVerify($token, $pin)
    {
        $Body['key'] = $this->OtpKey;
        $Body['secret'] = $this->OtpSecret;
        $Body['token'] = $token;
        $Body['pin'] = $pin;
        return $this->cUrl('POST', $this->UrlOtp . '/verify', null, $Body);
    }

    private function cUrl($Method, $Url, $Header = [], $Body = [], $isJson = true)
    {
        $Header = array_merge(['Accept:application/json'], $Header ?? []);
        $Header = $isJson ? array_merge(['Content-Type:application/json'], $Header) : $Header;
        $Body = $isJson ? json_encode($Body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $Body;
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
