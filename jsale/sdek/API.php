<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

class API
{

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

}