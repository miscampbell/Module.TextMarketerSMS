<?php

namespace Rhubarb\TextMarketerSms\SmsProviders;

use Rhubarb\Crown\Exceptions\SettingMissingException;
use Rhubarb\Crown\Http\CurlHttpClient;
use Rhubarb\Crown\Http\HttpRequest;
use Rhubarb\Crown\Sendables\Sendable;
use Rhubarb\Sms\Sendables\Sms\Sms;
use Rhubarb\Sms\Sendables\Sms\SmsProvider;
use Rhubarb\TextMarketerSms\Settings\TextMarketerSettings;

class TextMarketerSmsProvider extends SMSProvider
{
    const BASE_URL = "http://api.textmarketer.co.uk/services/rest/sms";

    public function send(Sendable $sendable)
    {
        /**
         * @var Sms $sendable
         */

        $settings = TextMarketerSettings::singleton();
        $username = $settings->username;
        $password = $settings->password;

        if ($username === null) {
            throw new SettingMissingException(TextMarketerSettings::class, "username");
        }
        if ($password === null) {
            throw new SettingMissingException(TextMarketerSettings::class, "password");
        }

        $payload = [
            "message" => $sendable->getText(),
            "username" => $username,
            "password" => $password,
            "originator" => $sendable->getSender()->name
        ];

        foreach ($sendable->getRecipients() as $smsNumber) {
            $tempPayload = ["mobile_number" => $smsNumber->number];

            $smsRequest = new HttpRequest(self::BASE_URL, "post", array_merge($payload, $tempPayload));
            $client = new CurlHttpClient($smsRequest);
            $client->getResponse($smsRequest);
        }
    }
}
