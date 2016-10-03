<?php

include_once dirname(__FILE__) . '/config.inc.php';
include_once dirname(__FILE__) . '/modules/M_DB.inc.php';
include_once dirname(__FILE__) . '/sdek/API.php';

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo json_encode(array());
}

$mDB = M_DB::Instance();

if($_GET['query']) {
    $cities = $mDB->autocompleteCity($_GET['query']);

    $result = array(
        'suggestions' => array()
    );

    foreach ($cities as $city) {
        $result['suggestions'][] = array('id' => $city['id'], 'value' => $city['city_name']);
    }

    echo json_encode($result);
}

if($_GET['city']) {
    $city = $mDB->checkCity($_GET['city']);

    if(count($city)) {
        $id = array_shift($city);
        echo json_encode(array('result' => true, 'id' => $id['id']));
    } else {
        echo json_encode(array('result' => false));
    }
}

if($_GET['pvz']) {
    $id = $_GET['id'];
    $api = new API();

    echo json_encode($api->getPvz($id), JSON_UNESCAPED_UNICODE);
}
exit;
