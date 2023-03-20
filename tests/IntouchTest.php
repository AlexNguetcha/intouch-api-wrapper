<?php

namespace AlexNguetcha\Intouch;

use PHPUnit\Framework\TestCase;

class IntouchTest extends TestCase
{

    private $intouch;

    public function __construct()
    {
        // load environnement variables
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../.env');
        $dotenv->load();
        $this->intouch = Intouch::credentials(
            username: $_ENV['DIGEST_AUTH_USERNAME'],
            password: $_ENV['DIGEST_AUTH_PASSWORD'],
            loginAgent: $_ENV['LOGIN_AGENT'],
            passwordAgent: $_ENV['PASSWORD_AGENT'],
            intouchId: $_ENV['INTOUCH_ID']
        )->callback('https://app.test/confirm-payment')
            ->amount(100)
            ->phone(695904403)
            ->operator('ORANGE')
            ->partnerId($partnerId)
            ->getBalance(
                [
                    "recipientEmail" => "nguetchaalex@gmail.com",
                    "recipientFirstName" => "Alex",
                    "recipientLastName" => "Nguetcha",
                ]
            );

        if ($intouch->isInitiated()) {
            // Your transaction has been initiated by intouch API
            echo ($intouch->getResult()->getBody());
        } else {
            // something went wrong
            // echo $intouch->getError()['request'];
            // echo $intouch->getError()['response'];
        }
    }

    public function testGetBalance()
    {
    }
}
