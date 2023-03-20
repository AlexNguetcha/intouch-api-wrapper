<?php

namespace AlexNguetcha\Intouch;

use Error;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7;

/**
 * Class Intouch
 * A PHP wrapper for Intouch API to perform cash-in, cash-out and balance transactions.
 * 
 * @package AlexNguetcha\Intouch
 */
class Intouch
{
    use IntouchTrait;
    /**
     * Intouch Cameroon Service Code for cash-in transaction
     */
    const OM_CASH_IN = "CASHINOMCMB2BDIST";
    const MOMO_CASH_IN = "CASHINMTNCM2_DYNATECH";

    /**
     * Intouch Cameroon Service Code for cash-out transaction
     */
    const OM_CASH_OUT = "CASHOUTOMCMB2BDIST";
    const MOMO_CASH_OUT = "CASHOUTMTNCM2_DYNATECH";

    /**
     * URL for merchant payment endpoint
     */
    private const GUTOUCH_API_URL = "https://api.gutouch.com/dist/api/touchpayapi/v1/[INTOUCH_ID]/transaction?loginAgent=[LOGIN_AGENT]&passwordAgent=[PASSWORD_AGENT]";

    /**
     * URL for cash-in payment endpoint
     */
    private const GUTOUCH_API_CASHIN_URL = "https://api.gutouch.com/v1/[INTOUCH_ID]/cashin";

    /**
     * URL for checking Intouch balance endpoint
     */
    private const GUTOUCH_API_GETBALANCE = "https://api.gutouch.com/v1/[INTOUCH_ID]/get_balance";

    /**
     * Supported ISP operators
     * 
     * @var array $SUPPORTED_OPERATORS
     */
    private const SUPPORTED_OPERATORS = ['ORANGE', 'MTN'];

    /**
     * @param string $username
     * @param string $password
     * 
     */
    private function __construct($username, $password, $loginAgent, $passwordAgent, $intouchId)
    {
        $this->username = $username;
        $this->password = $password;
        $this->loginAgent = $loginAgent;
        $this->passwordAgent = $passwordAgent;
        $this->intouchId = $intouchId;
    }

    public function getError()
    {
        return $this->apiError;
    }

    private function apiResult(mixed $result)
    {
        $this->apiResult = $result;
    }

    private function setError(mixed $error)
    {
        $this->apiError = $error;
    }

    public function getResult()
    {
        return $this->apiResult;
    }

    private function endpoint(string $endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    private function serviceCode(string $serviceCode)
    {
        $this->serviceCode = $serviceCode;
        return $this;
    }

    public function amount(string $amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function phone(string $phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function callback(string $url)
    {
        $this->callbackUrl = $url;
        return $this;
    }

    public function partnerId(string $partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    public function operator(string $operator)
    {
        $operator = strtoupper($operator ?? '');
        if (!in_array($operator, self::SUPPORTED_OPERATORS)) {
            throw new Exception("Unsupported operator: $operator, supported operators are " . implode(",", self::SUPPORTED_OPERATORS));
        }
        $this->operator = $operator;
        return $this;
    }

    public static function credentials(string $username, string $password, string $loginAgent, string $passwordAgent, $intouchId): Intouch
    {
        return new Intouch($username, $password, $loginAgent, $passwordAgent, $intouchId);
    }

    private function replaceUrlAgentCredentials(string $url): string
    {
        $url =  str_replace('[LOGIN_AGENT]', $this->loginAgent, $url);
        $url =  str_replace('[PASSWORD_AGENT]', $this->passwordAgent, $url);
        $url =  str_replace('[INTOUCH_ID]', $this->intouchId, $url);
        return $url;
    }

    private function setTheRighServiceCodeAndEndpoint($for = 'merchant' | 'cashin' | 'cashout' | 'balance')
    {
        // check the operator
        if ($for !== 'balance' && $this->operator === null) {
            throw new Exception('You must provide an operator for ' . $for . ' payment.');
        }

        // set the right service code
        switch ($for) {
            case 'merchant':
                if ($this->operator == self::SUPPORTED_OPERATORS[0]) {
                    // ORANGE
                    $this->serviceCode('CM_PAIEMENTMARCHAND_OM_TP');
                } else if ($this->operator == self::SUPPORTED_OPERATORS[1]) {
                    // MTN
                    $this->serviceCode('PAIEMENTMARCHAND_MTN_CM');
                }
                $this->endpoint($this->replaceUrlAgentCredentials(self::GUTOUCH_API_URL));
                break;
            case 'cashin':
                if ($this->operator == self::SUPPORTED_OPERATORS[0]) {
                    // ORANGE
                    $this->serviceCode('CASHINOMCMB2BDIST');
                } else if ($this->operator == self::SUPPORTED_OPERATORS[1]) {
                    // MTN
                    $this->serviceCode('CASHINMTNCM2_DYNATECH');
                }
                $this->endpoint($this->replaceUrlAgentCredentials(self::GUTOUCH_API_CASHIN_URL));
                break;
            case 'balance':
                $this->endpoint($this->replaceUrlAgentCredentials(self::GUTOUCH_API_GETBALANCE));
                break;
            default:
                # code...
                break;
        }
    }

    private function isValidPhoneNumber($phoneNumber): bool
    {
        return preg_match('/^6\d{8}$/', $phoneNumber) !== false;
    }

    private function isValidUrl($url): bool
    {
        return preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $url) !== false;
    }

    private function checkMiminumRequirements($for)
    {
        if (!is_numeric($this->amount) && in_array($for, ['merchant', 'cashin', 'cashout', 'balance'])) {
            throw new Exception('You must provide a valid amount for the transaction.');
        } else if (intval($this->amount) < 100) {
            throw new Exception('Transaction amount must be greather than 100 XAF');
        }

        if (is_null($this->partnerId) && in_array($for, ['cashin', 'balance'])) {
            throw new Exception('You must provide a valid Intouch partner id.');
        }

        if (!$this->isValidPhoneNumber($this->phone) && in_array($for, ['merchant', 'cashin', 'cashout', 'balance'])) {
            throw new Exception('You must provide a valid phone number 6abcdefgh.');
        }

        if (!$this->isValidUrl($this->callbackUrl) && in_array($for, ['merchant', 'cashin', 'cashout', 'balance'])) {
            throw new Exception('You must provide a valid callback url.');
        }
    }

    /**
     * Initiate a merchant payment
     * @param array additionnalInfos
     */
    public function makeMerchantPayment(array $additionnalInfos = []): Intouch
    {

        $this->checkMiminumRequirements('merchant');
        $this->setTheRighServiceCodeAndEndpoint('merchant');

        $payload = [
            'idFromClient' => $this->idFromClient ?? time(),
            'amount' => $this->amount,
            'callback' => $this->callbackUrl,
            'recipientNumber' => $this->phone,
            'serviceCode' => $this->serviceCode,
            'additionnalInfos' => $additionnalInfos
        ];

        $client = new Client([]);
        try {
            $result = $client->request('PUT', $this->endpoint, [
                'json' => $payload,
                'auth' => [$this->username, $this->password, 'digest'],
                'verify' => false
            ]);
            $this->apiResult($result);
        } catch (ConnectException $e) {
            $this->setError([
                'request' => Psr7\Message::bodySummary($e->getRequest(), 10000),
                'response' => 'connection cannot be established'
            ]);
        } catch (Exception $e) {
            $this->setError([
                'request' => Psr7\Message::bodySummary($e->getRequest(), 10000),
                'response' => Psr7\Message::bodySummary($e->getResponse(), 10000)
            ]);
        }
        return $this;
    }

    public function sendMoney()
    {

        $this->checkMiminumRequirements('cashin');
        $this->setTheRighServiceCodeAndEndpoint('cashin');

        $payload = [
            "service_id" => $this->serviceCode,
            "recipient_phone_number" => $this->phone,
            "amount" => $this->amount,
            "partner_id" => $this->partnerId,
            "partner_transaction_id" => $this->idFromClient ?? time(),
            "login_api" => $this->loginAgent,
            "password_api" => $this->passwordAgent,
            "call_back_url" => $this->callbackUrl
        ];

        $client = new Client([]);

        try {
            $result = $client->request('POST', $this->endpoint, [
                'json' => $payload,
                'auth' => [$this->username, $this->password, 'basic'],
                'verify' => false
            ]);
            $this->apiResult($result);
        } catch (ConnectException $e) {
            $this->setError([
                'request' => Psr7\Message::bodySummary($e->getRequest(), 10000),
                'response' => 'connection cannot be established'
            ]);
        } catch (Exception $e) {
            $this->setError([
                'request' => Psr7\Message::bodySummary($e->getRequest(), 10000),
                'response' => Psr7\Message::bodySummary($e->getResponse(), 10000)
            ]);
        }
        return $this;
    }

    public function getBalance()
    {
        $this->checkMiminumRequirements('balance');
        $this->setTheRighServiceCodeAndEndpoint('balance');

        $payload = [
            "partner_id" => $this->partnerId,
            "login_api" => $this->loginAgent,
            "password_api" => $this->passwordAgent,
        ];

        $client = new Client([]);

        $result = null;
        try {
            $result = $client->request('POST', $this->endpoint, [
                'json' => $payload,
                'auth' => [$this->username, $this->password, 'basic'],
                'verify' => false
            ]);
            // var_dump($result);
            $this->apiResult($result);
        } catch (ConnectException $e) {
            $this->setError([
                'request' => Psr7\Message::bodySummary($e->getRequest(), 10000),
                'response' => 'connection cannot be established'
            ]);
        } catch (Exception $e) {
            $this->setError([
                'request' => Psr7\Message::bodySummary($e->getRequest(), 10000),
                'response' => Psr7\Message::bodySummary($e->getResponse(), 10000)
            ]);
        }
        return $this;
    }

    public function isInitiated(): bool
    {
        if ($this->apiResult === null) return false;

        $body = (string)($this->apiResult->getBody());
        $result = get_object_vars(json_decode($body));
        $initiated =  is_array($result) && ((array_key_exists('status', $result) && $result['status'] === 'INITIATED')
            || array_key_exists('amount', $result));
        if (!$initiated) {
            $this->setError([
                'request' => array_key_exists('request', $this->getError()) ? $this->getError()['request'] : null,
                'response' => $result
            ]);
        }
        return $initiated;
    }
}
