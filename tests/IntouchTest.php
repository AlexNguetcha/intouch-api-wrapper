<?php

use PHPUnit\Framework\TestCase;
use AlexNguetcha\Intouch\Intouch;

class IntouchTest extends TestCase
{
    /**
     * intouch
     *
     * @var Intouch
     */
    private $intouch;

    public function setUp(): void
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../.env');
        $dotenv->load();
        $this->intouch = Intouch::credentials(
            $_ENV['DIGEST_AUTH_USERNAME'],
            $_ENV['DIGEST_AUTH_PASSWORD'],
            $_ENV['LOGIN_AGENT'],
            $_ENV['PASSWORD_AGENT'],
            $_ENV['INTOUCH_ID']
        );
    }

    public function testCredentials(): void
    {
        $this->assertInstanceOf(Intouch::class, $this->intouch);
    }

    public function testOperator(): void
    {
        $this->expectException(Exception::class);
        $this->intouch->operator('invalid_operator');
    }

    public function testGetBalance(): void
    {
        $result = $this->intouch->callback('https://app.test/confirm-payment')
            ->partnerId($_ENV['PARTNER_ID'])
            ->getBalance();
        $this->assertTrue($result->isInitiated());
    }

    public function testMakeMerchantPayment(): void
    {
        $amount = random_int(100, 500);
        if ($amount % 5 != 0) {
            $amount += $amount % 5;
        }
        $result = $this->intouch->callback('https://app.test/confirm-payment')
            ->amount($amount)
            ->phone($_ENV['PHONE'])
            ->operator('ORANGE')
            ->makeMerchantPayment([
                "recipientEmail" => "nguetchaalex@gmail.com",
                "recipientFirstName" => "Alex",
                "recipientLastName" => "Nguetcha",
            ]);
        $this->assertTrue($result->isInitiated());
    }
}
