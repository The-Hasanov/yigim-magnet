<?php

namespace Chameleon\YigimMagnet;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class Magnet
{
    private $merchant;

    private $secretKey;

    private $client;

    public const PARAM = [
        'REFERENCE'   => 'reference',
        'TYPE'        => 'type',
        'TOKEN'       => 'token',
        'SAVE'        => 'save',
        'AMOUNT'      => 'amount',
        'CURRENCY'    => 'currency',
        'BILLER'      => 'biller',
        'DESCRIPTION' => 'description',
        'TEMPLATE'    => 'template',
        'LANGUAGE'    => 'language',
        'CALLBACK'    => 'callback',
        'EXTRA'       => 'extra',
    ];

    public const LANG = [
        'AZ' => 'az',
        'EN' => 'en',
        'RU' => 'ru',
    ];

    public const CURRENCY = [
        'AZN' => 944,
        'USD' => 840,
        'EUR' => 978,
    ];

    public const CODE = [
        'S0' => 'S0',
        'S1' => 'S1',
        'S2' => 'S2',
        'S3' => 'S3',
        'S4' => 'S4',
        'S5' => 'S5',
        'S7' => 'S7',
        '00' => '00',
        '01' => '01',
        '02' => '02',
        '03' => '03',
        '04' => '04',
        '05' => '05',
        '06' => '06',
        '07' => '07',
        '08' => '08',
        '09' => '09',
        '0'  => '0',
        '1'  => '1',
        '2'  => '2',
        '3'  => '3',
        '4'  => '4',
        '5'  => '5',
        '6'  => '6',
        '10' => '10',
        '20' => '20',
        '21' => '21',
        '22' => '22',
        '23' => '23',
        '24' => '24',
        '25' => '25',
        '30' => '30',
    ];

    public const SUCCESS_CODE = [
        self::CODE['S1'],
        self::CODE['S4'],
        self::CODE['S5'],
        self::CODE['00'],
        self::CODE['0'],
    ];

    public const ERROR_CODE = [
        self::CODE['S3'],
        self::CODE['S7'],
        self::CODE['01'],
        self::CODE['02'],
        self::CODE['03'],
        self::CODE['04'],
        self::CODE['05'],
        self::CODE['06'],
        self::CODE['07'],
        self::CODE['08'],
        self::CODE['1'],
        self::CODE['2'],
        self::CODE['3'],
        self::CODE['4'],
        self::CODE['5'],
        self::CODE['6'],
        self::CODE['20'],
        self::CODE['22'],
        self::CODE['23'],
        self::CODE['24'],
        self::CODE['25'],
    ];

    public const PENDING_CODE = [
        self::CODE['S0'],
        self::CODE['S2'],
        self::CODE['09'],
        self::CODE['10'],
        self::CODE['21'],
        self::CODE['30'],
    ];

    public static function create($config): Magnet
    {
        return new self($config['merchant'], $config['key'], new Client([
            'base_uri' => $config['host'],
            'timeout'  => 30,
            'verify'   => false
        ]));
    }

    public function __construct($merchant, $secretKey, ClientInterface $client)
    {
        $this->merchant = $merchant;
        $this->secretKey = $secretKey;
        $this->client = $client;
    }

    public function createPayment($parameters)
    {
        return $this->sendGetRequestWithParamters('payment/create', $parameters);
    }

    public function statusPayment($parameters)
    {
        return $this->sendGetRequestWithParamters('payment/status', $parameters);
    }

    public function executePayment($parameters)
    {
        return $this->sendGetRequestWithParamters('payment/execute', $parameters);
    }

    public function chargePayment($parameters)
    {
        return $this->sendGetRequestWithParamters('payment/charge', $parameters);
    }

    public function cancelPayment($parameters)
    {
        return $this->sendGetRequestWithParamters('payment/cancel', $parameters);
    }

    public function refundPayment($parameters)
    {
        return $this->sendGetRequestWithParamters('payment/refund', $parameters);
    }

    public function batchClose($parameters)
    {
        return $this->sendGetRequestWithParamters('merchant/batch/close', $parameters);
    }

    private function sendGetRequestWithParamters($action, $parameters)
    {
        $parameters = $this->orderParameters($parameters);
        return $this->requestToApi('GET', $action, [
            'query' => $parameters,
        ], [
            'X-Signature' => $this->signature($parameters),
        ]);
    }

    protected function prepareOptions(array $options = [], array $headers = []): array
    {
        $options = array_merge([
            'strict'          => false,
            'referer'         => false,
            'track_redirects' => true,
            'http_errors'     => false,
            'protocols'       => ['http', 'https']
        ], $options);


        $options['headers'] = array_merge([
            'User-Agent' => 'YigimMagnetPHP',
            'X-Merchant' => $this->merchant,
            'X-Type'     => 'JSON'
        ], $options['headers'] ?? [], $headers);

        return $options;
    }

    protected function requestToApi($method, $uri, array $options = [], array $headers = []): ?array
    {
        $response = $this->client
            ->request($method, ltrim($uri, '/'), $this->prepareOptions($options, $headers));

        return json_decode($response->getBody(), true);
    }

    protected function orderParameters($parameters)
    {
        $ordered = [];
        foreach (static::PARAM as $parameterName) {
            if (array_key_exists($parameterName, $parameters)) {
                $ordered[$parameterName] = $parameters[$parameterName];
            }
        }
        return $ordered;
    }

    protected function signature($parameters): string
    {
        return base64_encode(
            md5(implode($parameters) . $this->secretKey, true)
        );
    }
}
