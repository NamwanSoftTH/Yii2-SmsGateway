# Yii2-SmsGateway

## Installation

```
composer require namwansoft/yii2-sms-gateway "@dev"
```

## Usage

```php

$arSms = (object) [
    'sender_name'   => '',
    'key_api'       => '',
    'key_secret'    => '',
    'OtpKey'        => '',
    'OtpSecret'     => '',
];

```

### ThaiBulkSms

visit site [ThaiBulkSms](https://www.thaibulksms.com/)

```php

$SmsGateway = new \namwansoft\SmsGateway\ThaiBulkSms($arSms);

$SmsGateway->setProxy('ip:port','user:pass');

$SmsGateway->credit();

$SmsGateway->Send('msisdn',['message' => 'message']);

$SmsGateway->OTP('msisdn');

$SmsGateway->OTPVerify('token', 'pin');

```

### SmsMKT

visit site [SmsMKT](https://smsmkt.com/)

```php

$SmsGateway = new \namwansoft\SmsGateway\SmsMKT($arSms);

$SmsGateway->setProxy('ip:port','user:pass');

$SmsGateway->credit();

$SmsGateway->Send('msisdn',['message' => 'message']);

$SmsGateway->OTP('msisdn');

$SmsGateway->OTPVerify('token', 'pin');

```
