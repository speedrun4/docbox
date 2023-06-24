<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/RequestController.php");

use Docbox\control\RequestController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use stdClass;

$user = getUserLogged();
if ($user == NULL) {
    exit();
}

$response = new stdClass();
$response->ok = FALSE;
$response->response = "error";

$docs = isset($_POST['docs']) ? $_POST['docs'] : array();

if (count($docs) > 0) {
    $db = new DbConnection();
    $reqController = new RequestController($db);

    if ($id = $reqController->registerDocumentRequest($docs, $user)) {
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