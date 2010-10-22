<?php

require_once 'Taobao.php';
$objTaobao = new Taobao(APP_ID, APP_SECRET);
$objTaobao->setSessionKey($_GET['top_session']);
try {
    $result = $objTaobao->api('taobao.jianghu.user.getProfile');
} catch (Exception $e) {
    return;
}   
var_dump($result);
