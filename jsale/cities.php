<?php

include_once dirname(__FILE__) . '/config.inc.php';
include_once dirname(__FILE__) . '/modules/M_DB.inc.php';

$mDB = M_DB::Instance();

$cities = $mDB->ajaxCity($_GET['query']);

$result = array(
    'suggestions' => array()
);

foreach ($cities as $city) {
    $result['suggestions'][] = array('id' => $city['id'], 'value' => $city['city_name']);
}

echo json_encode($result);

exit;