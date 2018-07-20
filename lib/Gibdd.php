<?php

namespace bckr75;

use Curl\Curl;

class Gibdd
{

    const HOST = 'http://check.gibdd.ru';
    const CHECK_PATH = '/proxy/check/auto/';
    const CAPTCHA_PATH = '/proxy/captcha.jpg';
    protected static $_checkMethods = [
        'history' => [
            'history' => [
                'vehicle' => [
                    'vehicle' => [
                        'model' => 'model',
                        'vin' => 'vin',
                        'bodyNumber' => 'bodyNumber',
                        'category' => 'category',
                        'bodyColor' => 'color',
                        'chassisNumber' => 'chassisNumber',
                        'engineNumber' => 'engineNumber',
                        'powerKwt' => 'powerKwt',
                        'engineDisp' => 'engineVolume',
                        'engineHp' => 'powerHp',
                        'vehicleType' => 'type',
                        'year' => 'year'
                    ]
                ],
                'periods' => [
                    'ownershipPeriods.ownershipPeriod' => [
                        'each' => [
                            'from' => 'from',
                            'to' => 'to',
                            'operation' => 'lastOperation',
                            'ownerType' => 'simplePersonType'
                        ]
                    ]
                ],
                'passport' => [
                    'vehiclePassport' => [
                        'issue' => 'issue',
                        'number' => 'number'
                    ]
                ]
            ]
        ],
        'dtp' => [
            'aiusdtp' => [
                'this' => [
                    'Accidents' => [
                        'each' => [
                            'date' => 'AccidentDateTime',
                            'number' => 'AccidentNumber',
                            'type' => 'AccidentType',
                            'damagePoints' => 'DamagePoints',
                            'region' => 'RegionName',
                            'damageState' => 'VehicleDamageState',
                            'brand' => 'VehicleMark',
                            'model' => 'VehicleModel',
                            'year' => 'VehicleYear',
                        ]
                    ]
                ]
            ]
        ],
        'restrict' => [
            'restricted' => [
                'this' => [
                    'records' => [
                        'each' => [
                            'vehicle' => 'tsmodel',
                            'year' => 'tsyear',
                            'dateAdd' => 'dateogr',
                            'region' => 'regname',
                            'divType' => 'divtype',
                            'restType' => 'ogrkod'
                        ]
                    ]
                ]
            ]
        ],
        'wanted' => [
            'wanted' => [
                'this' => [
                    'records' => [
                        'each' => [
                            'vehicle' => 'w_model',
                            'year' => 'w_god_vyp',
                            'dateAdd' => 'w_data_pu',
                            'region' => 'w_reg_inic'
                        ]
                    ]
                ]
            ]
        ]
    ];

    public $raw = [];
    public $debug = [];
    private $_curl;

    /**
     * Default parameters in case you're initializing class without any.
     * @var array $_params
     * */
    private $_params = [
        'timeout' => 30,
        'useragent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 ' .
            '(KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
        'host' => self::HOST,
        'check_path' => self::CHECK_PATH,
        'captcha_path' => self::CAPTCHA_PATH,
        'referrer' => self::HOST . self::CAPTCHA_PATH
    ];

    /**
     * Returns all four check methods in case you want to do something with it
     * (e.g. write your own function to override default tryGetSmth)
     * @return array
     */
    public static function getCheckMethods() {
        return array_keys(self::$_checkMethods);
    }

    /**
     * Gibdd constructor. Default parameters are described above in $_params.
     * It's strongly recommended to use proxy in parsing,
     * so $params may contain array 'proxy' with 'address' => 'ip:port' and 'userpass' => 'username:password'
     *
     * @param array|null $params
     *
     * @throws \ErrorException
     */
    function __construct(array $params = null) {
        if (!empty($params)) {
            $this->_params = array_replace_recursive($this->_params, $params);
        }
        $this->_curl = new Curl();
        $this->_curl->setJsonDecoder(function ($json) {
            return json_decode($json, true);
        });
        $this->_curl->setDefaultDecoder(function ($json) {
            return json_decode($json, true);
        });
        if (isset($this->_params['proxy']) && !empty($proxy = $this->_params['proxy'])) {
            $this->_curl->setOpt(CURLOPT_PROXY, $proxy['address']);
            $this->_curl->setOpt(CURLOPT_PROXYUSERPWD, $proxy['userpass']);
        }
        $this->_curl->setOpt(CURLOPT_USERAGENT, $this->_params['useragent']);
        $this->_curl->setOpt(CURLOPT_REFERER, $this->_params['referrer']);
        $this->_curl->setOpt(CURLOPT_CONNECTTIMEOUT, $this->_params['timeout']);
        $this->_curl->setHeader('Origin', $this->_params['host']);
    }

    /**
     * @param string $vin
     * @param string $captcha
     *
     * @return bool
     * @throws \Exception
     */
    function tryGetHistory($vin, $captcha) {
        return $this->exec('history', $vin, $captcha);
    }

    /**
     * @param string $vin
     * @param string $captcha
     *
     * @return bool
     * @throws \Exception
     */
    function tryGetDtp($vin, $captcha) {
        return $this->exec('dtp', $vin, $captcha);
    }

    /**
     * @param string $vin
     * @param string $captcha
     *
     * @return bool
     * @throws \Exception
     */
    function tryGetIsWanted($vin, $captcha) {
        return $this->exec('wanted', $vin, $captcha);
    }

    /**
     * @param string $vin
     * @param string $captcha
     *
     * @return bool
     * @throws \Exception
     */
    function tryGetRestrictions($vin, $captcha) {
        return $this->exec('restrict', $vin, $captcha);
    }


    /**
     * Returns captcha as is or base64 encoded.
     * For further check we will need to get 'JSESSIONID' cookie, so:
     * @param array $options
     * This array may contain next two parameters:
     * 'setCookie' and 'base64'.
     * setCookie - by default cookie sets only in $this->curl, so if you need to make a new Gibdd class everytime,
     * you need to set cookie in browser. This is what this option responsible for.
     * base64 - in case you want to return base64 encoded captcha.
     * @return string
     * @throws \Exception
     */
    public function getCaptchaValue($options = null) {
        $this->_curl->get(self::HOST . self::CAPTCHA_PATH);
        if ($this->_curl->error) {
            throw new \Exception('Getting page fail: ' . $this->_curl->errorMessage);
        }
        if (empty($cookie = $this->_curl->getResponseCookie('JSESSIONID')) &&
            empty($cookie = $this->_curl->getCookie('JSESSIONID'))) {
            throw  new \Exception('Getting session cookie fail');
        }
        $this->_curl->setCookie('JSESSIONID', $cookie);
        if (isset($options['setCookie']) && $options['setCookie']) {
            setcookie('JSESSIONID', $cookie, time() + 30, '/');
        }
        return (isset($options['base64']) && $options['base64']) ?
            'data:image/png;base64,' . base64_encode($this->_curl->rawResponse) : $this->_curl->rawResponse;
    }

    /**
     * Core execution function, returns bool value, execution result will be in $this->raw if there were no errors,
     * else $this->debug will speak for itself.
     * @param string $page
     * @param string $vin
     * @param string $captcha
     * @return bool
     * @throws \Exception
     */
    private function exec($page, $vin, $captcha) {
        $type = array_keys(self::$_checkMethods[$page])[0];
        $tpl = self::$_checkMethods[$page][$type];
        if (!$type) {
            throw new \Exception("Type for \"$page\" doesn't exist.");
        }
        if (!$this->_curl->getCookie('JSESSIONID')) {
            if (!empty($_COOKIE['JSESSIONID'])) {
                $this->_curl->setCookie('JSESSIONID', $_COOKIE['JSESSIONID']);
            } else {
                throw new \Exception('Cookie "JSESSIONID" doesn\'t exist');
            }
        }
        $this->_curl->post(self::HOST . self::CHECK_PATH . $page, [
            'vin' => $vin,
            'captchaWord' => $captcha,
            'checkType' => $type
        ]);
        if ($this->_curl->response['status'] == 201) {
            $this->debug[$type][] = [
                'from' => __METHOD__ . '->' . $page,
                'message' => 'Invalid captcha',
                'code' => $this->_curl->response['status']
            ];
            return false;
        }
        if (!is_array($this->_curl->response['RequestResult'])) {
            $this->debug[$type][] = [
                'from' => __METHOD__ . '->' . $page,
                'message' => 'Invalid response',
                'code' => 500
            ];
            return false;
        }
        $this->raw[$type] = $this->convertRaw($this->_curl->response['RequestResult'], $tpl);
        return true;
    }

    /**
     * Filters raw array and converts it
     * @param array $raw
     * @param array $template
     * @return array
     */
    private function convertRaw($raw, $template) {
        $result = [];
        foreach ($template as $item => $value) {
            if (is_array($value)) {
                $rawName = array_keys($value)[0];
                if (strpos($rawName, '.')) {
                    $tree = explode('.', $rawName);
                    $raw[$rawName] = $raw;
                    foreach ($tree as $path) {
                        $raw[$rawName] = $raw[$rawName][$path];
                    }
                }
                if ($item === 'each') {
                    foreach ($raw as $item) {
                        $result[] = self::convertRaw($item, $value);
                    }
                    continue;
                }
                $result[$item] = self::convertRaw($raw[$rawName], $value[$rawName]);
                if ($item === 'this') {
                    $result = $result[$item];
                }
                continue;
            }
            if (!empty($raw[$value])) {
                $result[$item] = $raw[$value];
            }
        }
        return $result;
    }
}