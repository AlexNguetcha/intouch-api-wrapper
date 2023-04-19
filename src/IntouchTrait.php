<?php

namespace AlexNguetcha\Intouch;


trait IntouchTrait
{
    /**
     * Username and Password provided by Intouch API
     *
     * @var string $username
     * @var string $password
     */
    private $username;
    private $password;

    /**
     * Login Agent and Password Agent provided by Intouch API
     * @var string $loginAgent
     * @var string $passwordAgent
     */
    private $loginAgent;
    private $passwordAgent;

    /**
     * Partner ID
     * @var string|null $partnerId
     */
    private $partnerId = null;

    /**
     * Intouch ID
     * @var string $intouchId
     */
    private $intouchId;

    /**
     * Callback URL to call when payment is successful
     * @var string $callbackUrl
     */
    private $callbackUrl;

    /**
     * Amount of the payment
     * @var string $amount
     */
    private $amount;

    /**
     * Phone number of the recipient
     * @var string $phone
     */
    private $phone;

    /**
     * API endpoint
     * @var string $endpoint
     */
    private $endpoint;

    /**
     * Intouch's service code for payment
     * @var string $serviceCode
     */
    private $serviceCode;

    /**
     * ISP operator (e.g. ORANGE, MTN)
     * @var string $operator
     */
    private $operator;

    /**
     * ID provided by the client
     * @var string|null $idFromClient
     */
    private $idFromClient = null;

    private $apiResult = null;
    private $apiError = null;

    private $initiated = false;


    public function getUserName()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getLoginAgent()
    {
        return $this->loginAgent;
    }

    public function getPasswordAgent()
    {
        return $this->passwordAgent;
    }

    public function getIntouchId()
    {
        return $this->intouchId;
    }

    public function getPartnerId()
    {
        return $this->partnerId;
    }

    public function getCallBack()
    {
        return $this->callbackUrl;
    }

    public function getPhone()
    {
        return $this->phone;
    }
}
