<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../utils/Input.php");
include_once (dirname ( __FILE__ ) . "/../model/Request.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/DevolutionController.php");

use Docbox\control\DevolutionController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\utils\Input;
use DateTime;
use stdClass;
use Docbox\model\RequestType;

$user = getUserLogged ();

if ($user == NULL || ! $user->isAdmin ()) {
	exit ();
}

$response = new stdClass ();
$response->ok = false;
$response->response = "error";

if (isset ( $_GET ['files'] )) {
	$allowedExtensions = array (
			"pdf",
			"jpg",
			"png",
			"jpeg"
	);
	$token = "";
	$files = array ();

	// Cria o token do upload
	$token = sha1 ( "peach" . rand ( 1, 200 ) . "" . (new DateTime ( "now" ))->getTimestamp () );
	$uploaddir = dirname ( __FILE__ ) . "/../../temp_files/";
	if (! is_dir ( $uploaddir ))
		mkdir ( $uploaddir, 0755, true );
	// TODO Check file size....
	foreach ( $_FILES as $file ) {
		$extension = strtolower ( substr ( $file ['name'], strripos ( $file ['name'], "." ) + 1 ) );
		if (in_array ( $extension, $allowedExtensions )) {
			if (! in_array ( $file ['name'], $files )) {
				if (move_uploaded_file ( $file ['tmp_name'], $uploaddir . $token . "." . $extension )) {
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
	$ret_id = Input::int ( "ret" );
	$token = Input::str ( 'token' );

	if ($ret_id > 0 && ! empty ( $token )) {
		$db = new DbConnection ();
		$devolutionController = new DevolutionController ( $db );

		$filename = "";
		// Se o arquivo temporário existe...
		if (! empty ( $token )) {
			if (file_exists ( dirname ( __FILE__ ) . "/../../temp_files/$token" )) {
				$filename = $token;
				$token = dirname ( __FILE__ ) . "/../../temp_files/$token";
			} else {
				$response->type = "error";
				$response->error = "Erro no upload do arquivo!";
			}
		}

		$folder = dirname ( __FILE__ ) . "/../../devolution_files/";
		// if(!is_dir($folder)) mkdir($folder, 0777, true);

		if (rename ( $token, $folder . $filename )) {
			$devolution = $devolutionController->getDevolutionById ( $ret_id );
			if ($devolution != NULL) {
				$devOK = FALSE;
				if($devolution->getReqType() == RequestType::DOCUMENT) {
					$devOK = $devolutionController->finishDocumentDevolution ( $devolution, $filename, $user->getId () );
				} else if($devolution->getReqType() == RequestType::BOX) {
					$devOK = $devolutionController->finishBoxDevolution ( $devolution, $filename, $user->getId () );
				}
				if ($devOK) {
					$response->ok = TRUE;
					$response->type = "success";
					$response->response = "Devolução finalizada com sucesso!";
				} else {
					$response->type = "error";
					$response->response = "Erro ao finalizar devolução";
				}
			} else {
				$response->type = "error";
				$response->response = "Erro ao finalizar devolução";
			}
		} else {
			$response->type = "error";
			$response->response = "Erro ao alterar a devolução";
		}
	} else {
		$response->response = "Parâmetros incorretos";
	}
}

echo json_encode ( $response );