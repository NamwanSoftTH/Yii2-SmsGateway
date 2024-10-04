<?php

namespace namwansoft\SmsGateway;

class Index
{

    public function listProvider()
    {
        return [
            'ThaiBulkSms' => 'ThaiBulkSms',
            // 'THSms'       => 'THSms',
            'SmsMKT'      => 'SmsMKT',
            'Sms2Pro'     => 'Sms2Pro',
        ];
    }

    public function getProvider($arSms)
    {
        switch ($arSms->service) {
            case 'ThaiBulkSms':
                $Sms = new \namwansoft\SmsGateway\ThaiBulkSms($arSms);
                break;
            case 'SmsMKT':
                $Sms = new \namwansoft\SmsGateway\SmsMKT($arSms);
                break;
            case 'THSms':
                $Sms = new \namwansoft\SmsGateway\THSms($arSms);
                break;
            case 'Sms2Pro':
                $Sms = new \namwansoft\SmsGateway\Sms2Pro($arSms);
                break;
            default:
                $Sms = false;
        }
        return $Sms;
    }

}
