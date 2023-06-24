<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/BoxController.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use BoxController;
use stdClass;

$user = getUserLogged();
if($user == NULL) {
    exit();
}

$response = new  stdClass();
$response->ok = FALSE;
$response->message = "Não foi possível concluir a operação";

$box_from = getReqParam("box_number", "int", "get");

if($box_from > 0) {
    $db = new DbConnection();
    $boxController = new BoxController($db);
    $box = $boxController->getBox($user->getClient(), $box_from);

    if($box != NULL && $box->getClient() == $user->getClient()) {
        $response->ok = TRUE;
        $response->box = new stdClass();
        $response->box->id = $box->getId();
        $response->box->department = $box->getDepartment()->getName();
        $response->box->request = $box->getRequest() == NULL ? 0 : $box->getRequest()->getId();
    } else {
        $response->type = "warning";
        $response->message = "Caixa não encontrada";
    }
} else {
    $response->message = "Parâmetros incorretos";
    $response->type = "warning";
}

echo json_encode($response);