<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/BoxController.php");

use DocBox\model\RequestStatus;
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

$box = getReqParam("box", "int", "post");
$newBoxNumber = getReqParam("new_box_number", "int", "post");
$deleteContent = getReqParam("del_content", "boolean", "post");

if($box > 0) {
    $db = new DbConnection();
    $boxController = new BoxController($db);
    $box = $boxController->getBoxById($box);

    if($box != NULL && $box->getClient() == $user->getClient()) {
        // Se a caixa está em pedido não concluído
        if ($box->getRequest() == NULL || $box->getRequest()->getStatus() == RequestStatus::CANCELED 
                || $box->getRequest()->getStatus() == RequestStatus::RETURNED 
                    || $box->getRequest()->getStatus() == RequestStatus::COMPLETED) {
            if($boxController->isBoxEmpty($box) || $deleteContent) {
                if($boxController->deleteBox($box->getId(), $user->getId())) {
                    $response->ok = TRUE;
                }
            } else {
                // Apaga a caixa e envia os documentos para outra caixa
                $substituteBox = $boxController->getBox($user->getClient(), $newBoxNumber);
                if($substituteBox != NULL && $box->getId() != $substituteBox->getId()) {
                    if($boxController->transferDocuments($box, $substituteBox, $user->getId())) {
                        $boxController->deleteBox($box->getId(), $user->getId());
                        $response->ok = TRUE;
                    } else {
                        $response->message = "Erro ao transferir documentos";
                    }
                } else {
                    $response->type = "warning";
                    $response->message = "Caixa de destino inválida!";
                }
            }
        } else {
            $response->type = "warning";
            $response->message = "A caixa se encontra em pedido. Solucione a situação do pedido e tente novamente.";
        }
    } else {
        $response->type = "warning";
        $response->message = "Caixa não encontrada";
    }
} else {
    $response->message = "Parâmetros incorretos";
}

echo json_encode($response);