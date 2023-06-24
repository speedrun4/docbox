<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../model/User.php");
include_once (dirname(__FILE__) . "/../control/UserController.php");
include_once (dirname(__FILE__) . "/../control/RecoverPasswordController.php");
include_once (dirname(__FILE__) . "/../utils/Utils.php");

use Docbox\control\UserController;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use RecoverPasswordController;
use stdClass;

$response  = new stdClass();
$response->ok = FALSE;

$email = getReqParam("email", "str", "post");
$email = filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE);

if($email != NULL && !empty($email)) {
    $db = new DbConnection();
    $usrController = new UserController($db);

    $user = $usrController->getUserByEmail($email);

    if($user != NULL) {
        $recController = new RecoverPasswordController($db);
        $token = sha1(date('H:i') . "iching" . $user->getName());

        if($recController->insertToken($user, $token)) {
            $link = "http://" . $_SERVER['SERVER_NAME'] . "/trocar_senha.php?token=$token";
            if($recController->sendRecoverEmail($user->getName(), $user->getEmail(), $link)) {
                $response->ok = TRUE;
            } else {
                $response->msg = "Cant send email";
            }
        } else {
            $response->msg = "Cant insert token";
        }
    } else {
        $response->msg = "Usuario nao encontrado";
    }
} else {
    $response->msg = "Email invalido";
}

echo json_encode($response);