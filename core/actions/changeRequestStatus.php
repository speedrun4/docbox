<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/RequestController.php");
include_once (dirname(__FILE__) . "/../control/DevolutionController.php");
include_once (dirname(__FILE__) . "/../model/Request.php");

use Docbox\control\DevolutionController;
use Docbox\control\RequestController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\RequestStatus;
use function Docbox\utils\getReqParam;
use DateTime;
use stdClass;

$user = getUserLogged();

if($user == NULL) {
	exit();
}

$response = new  stdClass();
$response->ok = false;
$response->response = "error";

if(isset($_GET['files'])) {
    $allowedExtensions = array("pdf", "jpg", "png", "jpeg");
    $token = "";
    $files = array ();

    // Cria o token do upload
    $token = sha1("ffff..." . rand(1008, 20000) . "" . (new DateTime("now"))->getTimestamp());
    $uploaddir = dirname ( __FILE__ ) . "/../../temp_files/";
    if(!is_dir($uploaddir)) mkdir ( $uploaddir, 0755, true );

    foreach($_FILES as $file) {
        $extension = strtolower(substr($file['name'], strripos($file['name'], ".") + 1));
        if(in_array($extension, $allowedExtensions)) {
            if(!in_array($file['name'], $files)) {
                if(move_uploaded_file($file['tmp_name'], $uploaddir . $token . "." . $extension)) {
                    $response->ok = true;
                    $response->token = "$token.$extension";
                } else {
                    $response->ok = false;
                    $response->error = 'Ocorreu um erro ao realizar o upload dos arquivos.';
                }
            }
        } else {
            $response->ok = false;
            $response->error = 'Ocorreu um erro ao realizar o upload dos arquivos.';
        }
    }
} else {
    $req_id = getReqParam("r", "int", "post");
    $req_status = getReqParam('s', 'int', 'post');
    $token =  getReqParam('token', 'str', 'post');

    if($req_id > 0 && $req_status > 0 && $req_status <= RequestStatus::COMPLETED) {
    	$db = new DbConnection();
    	$reqController = new RequestController($db);
    	
    	$request = $reqController->getRequest($req_id);
    	if($request != NULL && $request->getStatus() != $req_status) {
    	    switch($req_status) {
    	        case RequestStatus::OPENED: {// Reabertura de pedido ;/ ?
    	            
    	        } break;

    	        case RequestStatus::CANCELED: {
    	            if($request->getStatus() == RequestStatus::OPENED || $request->getStatus() == RequestStatus::SENT) {
    	            	if ($reqController->setRequestStatus($request, RequestStatus::CANCELED, $user->getId())) {
        	                $response->ok = TRUE;
        	                $response->type = "success";
        	                $response->response = "Pedido cancelado com sucesso!";
        	            } else {
        	                $response->type = "error";
        	                $response->response = "Erro ao alterar o pedido";
        	            }
    	            } else {
    	                $response->type = "error";
    	                $response->response = "Pedido não pode ser cancelado, solicite a devolução dos documentos ou finalize o pedido.";
    	            }
    	        } break;

    	        case RequestStatus::SENT: {
    	            if($request->getStatus() == RequestStatus::OPENED) {
    	                if($reqController->setRequestStatus($request, RequestStatus::SENT, $user->getId())) {
        	                $response->ok = TRUE;
        	                $response->type = "success";
        	                $response->response = "Pedido enviado com sucesso!";
        	                $response->action = 'print';
        	            } else {
        	                $response->type = "error";
        	                $response->response = "Erro ao alterar o pedido";
        	            }
    	            } else {
    	                $response->type = "error";
    	                $response->response = "Alteração de pedido não permitida";
    	            }
    	        } break;

    	        case RequestStatus::ATTENDEND: {
    	            if($request->getStatus() == RequestStatus::SENT && !empty($token)) {
    	            	$filename = "";
    	            	// Se o arquivo temporário existe...
    	            	if (! empty($token)) {
    	            		if (file_exists(dirname(__FILE__) . "/../../temp_files/$token")) {
    	            			$filename = $token;
    	            			$token = dirname(__FILE__) . "/../../temp_files/$token";
    	            		} else {
    	            			$response->type = "error";
    	            			$response->error = "Erro no upload do arquivo!";
    	            		}
    	            	}
    	            	
    	            	$folder = dirname ( __FILE__ ) . "/../../request_files/";
    	            	// if(!is_dir($folder)) mkdir($folder, 0777, true);

    	            	if(rename($token, $folder . $filename)) {
    	            	    if($reqController->setRequestStatus($request, RequestStatus::ATTENDEND, $user->getId(), $filename)) {
	        	                $response->ok = TRUE;
	        	                $response->type = "success";
	        	                $response->response = "Alteração realizada com sucesso!";
	        	            } else {
	        	                $response->type = "error";
	        	                $response->response = "Erro ao alterar o pedido";
	        	            }
    	            	} else {
    	            		$response->type = "error";
    	            		$response->response = "Erro no upload do arquivo";
    	            	}
    	            } else {
    	                $response->type = "error";
    	                $response->response = "Erro ao alterar o pedido";
    	            }
    	        } break;

    	        case RequestStatus::RETURNED: {
    	            if($request->getStatus() == RequestStatus::ATTENDEND) {
    	            	$devController = new DevolutionController($db);
    	            	if($devController->registerTotalRequestDevolution($request, $user->getId())) {
    	                    $response->ok = TRUE;
    	                    $response->type = "success";
    	                    $response->response = "Devolução solicitada com sucesso!";
    	                } else {
    	                    $response->type = "warning";
    	                    $response->response = "O pedido ainda não foi atendido";
    	                }
    	            } else {
    	                $response->type = "warning";
    	                $response->response = "O pedido ainda não foi atendido";
    	            }
    	        } break;

    	        case RequestStatus::COMPLETED: {
    	        	$filename = "";
    	        	// Se o arquivo temporário existe...
    	        	if (! empty($token)) {
    	        		if (file_exists(dirname(__FILE__) . "/../../temp_files/$token")) {
    	        			$filename = $token;
    	        			$token = dirname(__FILE__) . "/../../temp_files/$token";
    	        		} else {
    	        			$response->type = "error";
    	        			$response->error = "Erro no upload do arquivo!";
    	        		}
    	        	}
    	        	
    	        	$folder = dirname ( __FILE__ ) . "/../../request_files/";
    	        	// if(!is_dir($folder)) mkdir($folder, 0777, true);
    	        	
    	        	if(rename($token, $folder . $filename)) {
	    	            if($request->getStatus() == RequestStatus::RETURNED) {
	    	                if($reqController->setRequestStatus($request, RequestStatus::COMPLETED, $user->getId(), $filename)) {
	    	                    $response->ok = TRUE;
	    	                    $response->type = "success";
	    	                    $response->response = "Pedido finalizado com sucesso!";
	    	                } else {
	    	                    $response->type = "error";
	    	                    $response->response = "Erro ao finalizar pedido";
	    	                }
	    	            } else {
	    	                $response->type = "warning";
	    	                $response->response = "O pedido precisa ser devolvido";
	    	            }
    	        	} else {
    	        		$response->type = "error";
    	        		$response->response = "Erro ao alterar o pedido";
    	        	}
    	        } break;
    	        default: {
    	            $response->type = "warning";
    	            $response->response = "Requisição não modificada";
    	        } break;
    	    }
    	} else {
    		$response->type = "warning";
    		$response->response = "Requisição não modificada";
    	}
    } else {
    	$response->response = "Parâmetros incorretos";
    }
}

echo json_encode($response);