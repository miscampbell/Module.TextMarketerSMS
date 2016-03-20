<?php

namespace Rhubarb\TextMarketerSms\SmsProviders;

use Rhubarb\Crown\Exceptions\SettingMissingException;
use Rhubarb\Crown\Http\CurlHttpClient;
use Rhubarb\Crown\Http\HttpRequest;
use Rhubarb\Crown\Sendables\Sendable;
use Rhubarb\Crown\Sendables\SMS\SMSProvider;
use Rhubarb\TextMarketerSms\Settings\TextMarketerSettings;

class TextMarketerSmsProvider extends SMSProvider
{
    const BASE_URL = "http://api.textmarketer.co.uk/services/rest/sms";

    public function send(Sendable $sendable)
    {
        /**
         * @var TextMarketerSettings
         */
        $settings = new TextMarketerSettings();
        $username = $settings->Username;
        $password = $settings->Password;

        if ($username === null) {
            throw new SettingMissingException(TextMarketerSettings::class, "Username");
        }
        if ($password === null) {
            throw new SettingMissingException(TextMarketerSettings::class, "Password");
        }

        $payload = [
            "message" => $sendable->getText(),
            "username" => $username,
            "password" => $password
        ];


        foreach ($sendable->getRecipients() as $smsNumber) {
            $tempPayload = ["mobile_number" => $smsNumber->number];

            $smsRequest = new HttpRequest(self::BASE_URL, "post", array_merge($payload, $tempPayload));
            $client = new CurlHttpClient($smsRequest);
            $response = $client->getResponse($smsRequest);
        }
    }
}
