<?php

use PHPUnit\Framework\TestCase;
use AlexNguetcha\Intouch\Intouch;

class IntouchTest extends TestCase
{
    private $intouch;

    public function setUp(): void
    {
        $this->intouch = Intouch::credentials('username', 'password', 'loginAgent', 'passwordAgent', 'intouchId');
    }

    public function testCredentials(): void
    {
        $this->assertInstanceOf(Intouch::class, $this->intouch);
    }

}
