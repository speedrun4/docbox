<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserController.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");

use Docbox\control\UserController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use Exception;
use stdClass;

$user = getUserLogged();
if($user == NULL || !$user->isAdmin()) {
    exit();
}

$response = new  stdClass();
$response->ok = FALSE;
$response->type = "error";
$response->message = "Não foi possível executar a operação";

$usr_id = getReqParam("user", "int", "post");

if($usr_id > 0) {
    $db = new DbConnection();
    $userController = new UserController($db);

    $delUser = $userController->getUserById($usr_id);

    if($delUser != NULL) {
        try {
            if($userController->deleteUser($delUser, $user->getId())) {
                $response->ok = true;
                $response->type = "success";
                $response->message = "Usuário excluído com sucesso!";
            } else {
                $response->type = "error";
                $response->message = "Não foi possível excluir o usuário.";
            }
        } catch (Exception $e) {
            // echo 'Exceção capturada: ',  $e->getMessage(), "\n";
        }
    } else {
        $response->type = "error";
        $response->message = "Usuário não encontrado";
    }
}

echo json_encode($response);