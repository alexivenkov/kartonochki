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
        $url = 'http://gw.edostavka.ru:11443/pvzlist.php';

        $result = $this->client->request('GET', $url, [
            'cityid' => $id
        ]);

        $xml = simplexml_load_string((string) $result->getBody(),"SimpleXMLElement", LIBXML_NOCDATA);
        /*$states = array();

        foreach ($xml->children() as $state) {
                $states[] = array('state' => (array) $state);
        }*/

        return json_encode($xml);
    }

}