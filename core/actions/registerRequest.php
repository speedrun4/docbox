<?php
include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/RequestController.php");

use Docbox\control\RequestController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;

$user = getUserLogged();
if ($user == NULL) {
    exit();
}

$response = new stdClass();
$response->ok = false;
$response->response = "error";

$boxes = isset($_POST['boxes']) ? $_POST['boxes'] : array();

if (count($boxes) > 0) {
    $db = new DbConnection();
    $reqController = new RequestController($db);

    if ($id = $reqController->registerBoxRequest($boxes, $user)) {
        if ($id > 0) {
            $response->ok = TRUE;
            $response->type = "success";
            $response->req_id = $id;
        }
    } else {
        $response->type = "warning";
        $response->error = "Não foi possível concluir a requisição";
    }
} else {
    $response->type = "error";
    $response->error = "Parâmetros incorretos";
}

echo json_encode($response);