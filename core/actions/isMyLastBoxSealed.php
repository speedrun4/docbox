<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/BoxController.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use BoxController;
use stdClass;

$userLogged = getUserLogged();
if($userLogged != NULL && $userLogged->getClient() > 0) {
    $response = new  stdClass();
    $response->sealed = TRUE;

    $db = new DbConnection();
    $boxController = new BoxController($db);
    $box = $boxController->getLastBoxTakenByUser($userLogged->id);

    if($box != NULL) {
        $response->number = $box->getNumber();
        $response->sealed = $box->isSealed();
    }

    echo json_encode($response);
} else {
    exit();
}