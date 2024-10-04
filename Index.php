<?php

namespace namwansoft\SmsGateway;

class Index extends \yii\base\Component
{

    public $listProvider;

    public function __construct()
    {
        parent::__construct();
        $this->listProvider = [
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
                return new ThaiBulkSms($arSms);
                break;
            case 'SmsMKT':
                return new SmsMKT($arSms);
                break;
            case 'THSms':
                return new THSms($arSms);
                break;
            case 'Sms2Pro':
                return new Sms2Pro($arSms);
                break;
            default:
        }
        return false;
    }

}
