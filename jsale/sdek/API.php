<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

class API
{

    const TYPE_STOCK_STOCK = 136;
    const TYPE_STOCK_DOOR = 137;
    const API_VERSION = 1.0;
    const AUTH_LOGIN = '42942b40521b67d34f9fb723edcd00f2';
    const AUTH_PASS = 'ccd97034efad24601864d0480ce94093';
    const SENDER_CITY_ID = 237;

    protected $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public function getPvz($id)
    {
        $url = "http://gw.edostavka.ru:11443/pvzlist.php?cityid=$id";

        $result = $this->client->get($url);

        $xml = new SimpleXMLElement((string)$result->getBody());

        $result = array();

        foreach ($xml as $node) {
            $result[] = current($node->attributes());
        }

        return $result;
    }

    public function calculateDeliveryCost($params) {
        $url = 'http://api.edostavka.ru/calculator/calculate_price_by_json.php';

        $secure = md5($params['dateExecute']. '&'. self::AUTH_PASS);
        $data = array(
            'version' => self::API_VERSION,
            'authLogin' => self::AUTH_LOGIN,
            'secure' => $secure,
            'senderCityId' => self::SENDER_CITY_ID,
        );
    }
}