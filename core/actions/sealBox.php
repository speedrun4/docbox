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

$box = getReqParam("box_id", "int", "get");
$box_number = getReqParam('box_number', 'int', 'get');
$sealed = getReqParam("sealed", "boolean", "get");

if($box > 0 || $box_number > 0) {
    $db = new DbConnection();
    $boxController = new BoxController($db);
    $box = $box == 0 ? $boxController->getBox($user->getClient(), $box_number) : $boxController->getBoxById($box);

    if($box != NULL && $box->getClient() == $user->getClient()) {
        if($boxController->sealBox($box->getId(), $sealed, $user->getId())) {
            $response->ok = TRUE;
        } else {
            $response->type = "error";
        }
    } else {
        $response->type = "warning";
        $response->message = "Caixa não encontrada";
    }
} else {
    $response->message = "Parâmetros incorretos";
}

echo json_encode($response);