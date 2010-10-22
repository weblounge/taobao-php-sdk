<?php
/**
 * PHP SDK for the Taobao Open API
 *
 * Source was initially based on the Facebook PHP SDK.
 */

if (!function_exists('curl_init')) {
    throw new Exception('Taobao needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new Exception('Taobao needs the JSON PHP extension.');
}

class Taobao {

    const VERSION = '2.0';

    /**
     * Default options for curl.
     *
     * @var array
     */
    private static $CURL_OPTS = array (
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'taobao-php-api-2.0'
    );

    /**
     * The Application ID.
     *
     * @var integer
     */
    private $appId;

    /**
     * The Application API Secret.
     *
     * @var string
     */
    private $appSecret;

    /**
     * Test or not.
     *
     * @var boolean
     */
    private $test = false;

    /**
     * The Application Session Key.
     *
     * @var string
     */
    private $sessionKey;

    /**
     * Initialize a Taobao Application.
     *
     * @param integer $appId
     * @param string $appSecret
     * @return Taobao
     */
    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * @return boolean the $test
     */
    public function getTest() {
        return $this->test;
    }

    /**
     * @param boolean $test the $test to set
     */
    public function setTest($test) {
        $this->test = $test;
    }

    /**
     * @return the $sessionKey
     */
    public function getSessionKey() {
        return $this->sessionKey;
    }

    /**
     * @param $sessionKey the $sessionKey to set
     */
    public function setSessionKey($sessionKey) {
        $this->sessionKey = $sessionKey;
    }

    /**
     *
     * @param string $method
     * @param array The APP Params.
     * @throws TaobaoAPIException
     * @return the decoded response object
     */
    public function api($method, array $app_params = null) {
        $params = array (
            'app_key' => $this->appId,
            'method' => $method,
            'format' => 'json',
            'v' => self::VERSION,
            'timestamp' => date('Y-m-d H:i:s'),
            'session' => $this->sessionKey,
            'sign_method' => 'md5',
        );

        if ($app_params) $params = array_merge($params, $app_params);

        $params['sign'] = self::createSign($params, $this->appSecret);

        $api_gateway_url = $this->test ? 'http://gw.api.tbsandbox.com/router/rest' : 'http://gw.api.taobao.com/router/rest';
        return json_decode(self::makeRequest($api_gateway_url, $params), true);
    }

    /**
     *
     * @param array $params
     * @param string $appSecret
     * @return string $sign
     */
    private static function createSign(array $params, $appSecret) {
        ksort($params);
        $value_str = '';
        foreach ($params as $key => $val ) {
            if ($key != '' && $val != '') {
                $value_str .= $key . $val;
            }
        }
        return strtoupper(md5($appSecret . $value_str . $appSecret));
    }

    /**
     * Makes an HTTP request.
     *
     * @param string $url the URL to make the request to
     * @param array $params the parameters to use for the POST body
     * @param string $method 'GET' or 'POST'
     * @throws TaobaoAPIException
     * @return string the response text
     */
    private static function makeRequest($url, $params, $method = 'GET') {
        $ch = curl_init();

        $opts = self::$CURL_OPTS;

        if ($method === 'POST') {
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $params;
        } else if ($method === 'GET') {
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
        }

        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        if ($result === false) {
            $e = new TaobaoAPIException(curl_error($ch), curl_errno($ch));
            curl_close($ch);
            throw $e;
        }
        curl_close($ch);
        return $result;
    }
}

class TaobaoAPIException extends Exception {}
