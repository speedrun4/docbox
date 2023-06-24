<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../utils/Input.php");
include_once (dirname ( __FILE__ ) . "/../model/Withdrawal.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/BoxController.php");
include_once (dirname ( __FILE__ ) . "/../model/WithdrawalStatus.php");
include_once (dirname ( __FILE__ ) . "/../control/DocumentController.php");
include_once (dirname ( __FILE__ ) . "/../control/WithdrawalController.php");
include_once (dirname ( __FILE__ ) . "/../control/DocumentTypeController.php");

use function Docbox\control\getUserLogged;
use Docbox\control\WithdrawalController;
use Docbox\model\WithdrawalStatus;
use Docbox\model\DbConnection;
use Docbox\utils\Input;
use DateTime;
use stdClass;

$user = getUserLogged ();
if ($user == NULL || $user->getClient () <= 0) {
	exit ();
}

$response = new stdClass ();
$response->ok = false;

if (isset ( $_GET ['files'] )) {
	$withdrawalOK = FALSE;
	$withdrawalID = isset ( $_REQUEST ['pul_id'] ) ? intval ( $_REQUEST ['pul_id'] ) : 0;
	if ($withdrawalID > 0) {
		$db = new DbConnection ();
		$withController = new WithdrawalController($db);

		$withdrawal = $withController->getWithdrawalById($withdrawalID, $user->getClient ());

		if ($withdrawal != NULL && $withdrawal->getStatus() != WithdrawalStatus::CANCELLED) {
			$withdrawalOK = TRUE;
		}
	}

	if (count ( $_FILES ) == 1) {
		if ($withdrawalOK) {
			$allowedExtensions = array ("pdf");
			$token = "";
			$files = array ();

			// Cria o token do upload
			$token = sha1 ( "#token#" . rand ( 1, 1000 ) . "" . (new DateTime ( "now" ))->getTimestamp () );
			$uploaddir = dirname ( __FILE__ ) . "/../../withdrawal_files/";
			if (! is_dir ( $uploaddir )) {
				mkdir ( $uploaddir, 0777, TRUE );
			}

			foreach ( $_FILES as $file ) {// Deve ser somente um arquivo upload
				if ($file ["size"] < MAX_UPLOAD_MB * MB) {
					$extension = strtolower ( substr ( $file ['name'], strripos ( $file ['name'], "." ) + 1 ) );

					if (in_array ( $extension, $allowedExtensions )) {
						if (! in_array ( $file ['name'], $files )) {
							if (move_uploaded_file ( $file ['tmp_name'], $uploaddir . "$token.$extension" )) {
								$response->ok = TRUE;
								$response->token = $token.".$extension";
							} else {
								$response->ok = FALSE;
								$response->error = 'Ocorreu um erro ao realizar o upload dos arquivos.';
							}
						}
					} else {
						$response->ok = false;
						$response->type = "error";
						$response->error = 'Esta não é uma extensão permitida!';
					}
				} else {
					// Do not upload
					$response->ok = false;
					$response->type = "error";
					$response->error = 'O arquivo excede o tamanho máximo permitido (' . MAX_UPLOAD_MB . 'Mb)!';
				}
			}
		} else {
			$response->ok = false;
			$response->type = "error";
			$response->error = 'Não é possível adicionar arquivo à retirada';
		}
	} else {
		$response->ok = false;
		$response->type = "error";
		$response->error = 'Parâmetros incorretos';
	}

	echo json_encode ( $response );
} else {
	$withdrawalID = Input::int("r");
	$token = Input::str("token");

	// Se o arquivo temporário existe...
	if (! empty ( $token )) {
		if (file_exists ( dirname ( __FILE__ ) . "/../../withdrawal_files/$token" )) {
			$token = "./withdrawal_files/$token";
		} else {
			$response->type = "error";
			$response->error = "Erro no upload do arquivo!";
		}
	}

	if ($withdrawalID > 0) {
		$db = new DbConnection ();
		$withController = new WithdrawalController($db);
		$withdrawal = $withController->getWithdrawalById($withdrawalID, $user->getClient ());

		if($withdrawal != NULL) {
			if($withdrawal->getStatus() == WithdrawalStatus::OPEN) {
				if($withController->finishWithdrawal($withdrawalID, $token, $user->getId())) {
					$response->ok = TRUE;
					$response->type = "success";
					$response->message = "Retirada finalizada com sucesso";
				}
			} else if($withdrawal->getStatus() == WithdrawalStatus::FINISHED) {
				// Atualiza somente o comprovante
				if($withController->updateReceipt($withdrawalID, $token, $user->getId())) {
					$response->ok = TRUE;
					$response->type = "success";
					$response->message = "Comprovante atualizado com sucesso";
				}
			}
		} else {
			$response->type = "error";
			$response->error = "Não foi possível fechar a retirada";
		}
	} else {
		$response->ok = FALSE;
		$response->error = "Retirada não encontrada";
	}
	echo json_encode ( $response );
}