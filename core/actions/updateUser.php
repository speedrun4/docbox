<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../model/User.php");
include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/UserController.php");
include_once (dirname ( __FILE__ ) . "/../model/User.php");

use Docbox\control\DepartmentController;
use Docbox\control\UserController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;
use function Docbox\utils\createToken;
use function Docbox\utils\getReqParam;
use function Docbox\utils\validatePassword;
use stdClass;

$userLogged = getUserLogged();
if ($userLogged == NULL || $userLogged->getClient() == NULL || $userLogged->getClient () <= 0) {
	exit();
}

$response = new stdClass();
$response->ok = false;
$response->type = "error";

if (isset($_GET['files'])) {
    $usr_id = getReqParam("user", "int", "post");

	$allowedExtensions = array("jpg", "jpeg", "gif", "png");
	$token = "";
	$files = array ();

	// Cria o token do upload
	$token = createToken($usr_id);
	$uploaddir = dirname ( __FILE__ ) . "/../../photos/";
	if(!is_dir($uploaddir)) mkdir($uploaddir, 0777, true);

	foreach($_FILES as $file) {
		$extension = strtolower(substr($file['name'], strripos($file['name'], ".") + 1));
		if(in_array($extension, $allowedExtensions)) {
			if(!in_array($file['name'], $files)) {
				if(move_uploaded_file($file['tmp_name'], $uploaddir . $token . ".jpg")) {
					$response->ok = true;
					$response->token = $token . ".jpg";
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
	$usr_id = getReqParam("user", "int", "post");
	$profile = getReqParam("userprofile", "int", "post");
	$name = getReqParam("username", "str", "post");
	$login = getReqParam("login", "str", "post");

	$password = getReqParam("password", "str", "post");
	$cpassword = getReqParam("confpassword", "str", "post");
	$hasPassword = false;

	if(!empty($password) && !empty($cpassword)) {
		if(mb_strlen($password) < 64 && strcmp($password, $cpassword) == 0) {
			$hasPassword = validatePassword($password);
			if($hasPassword) $password = sha1($password);
		}
	}

	$email = getReqParam("email", "str", "post");
	$token = getReqParam("user_token", "str", "post");

	if(empty($token)) $token = NULL;

	$db = new DbConnection();

	// Pega os departamentos
	$departments = array();
	if($profile == User::USER_COMMON) {
		$departments = isset($_POST['departments']) ? $_POST['departments'] : array();
		if(count($departments) > 0) {
			$depController = new DepartmentController($db);
			foreach($departments as $department) {
				if($depController->getDepartmentById(intval($department)) == NULL) {
					$response->type = "error";
					$response->error = "Dados inconsistentes";
					echo json_encode($response);
					exit;
				}
			}
		} else {
			$response->type = "error";
			$response->error = "Informe o departamento corretamente";
			echo json_encode($response);
			exit;
		}
	}

	if ($usr_id > 0 && $profile >= User::USER_ADMIN && $profile <= User::USER_COMMON && !empty($name)) {
		if(mb_strlen($name) < 64 && mb_strlen($name) < 64 && mb_strlen($login) < 64) {
			$userController = new UserController($db);
			$user = $userController->getUserById($usr_id);

			if($user != NULL) {
				$user = new User();
				$user->setId($usr_id);
				$user->setName($name);
				$user->setLogin($login);
				$user->setProfile($profile);
				if ($hasPassword) {
					$user->setPassword ( $password );
				}
				$user->setPhoto($token);
				$user->setEmail($email);
				if ($profile == User::USER_COMMON) {
					$user->setClient($userLogged->getClient());
				} else if($profile == User::USER_ADMIN){
					$user->setClient(NULL);
				}

				$usrDb = $userController->getUserByLogin($login, $userLogged->getClient());

				if($usrDb == NULL || $usrDb->getId() == $user->getId()) {
					// Grava os departamentos do usuário
					$userController->setUserDepartments($user->getId(), $departments);

					if ($userController->updateUser($userLogged->getId(), $user)) {
						$response->ok = TRUE;
						$response->type = "success";
					} else {
						$response->error = "Erro ao realizar alteração";
						$response->type = "error";
					}
				} else {
					$response->type = "warning";
					$response->error = "Já existe um outro usuário com este login!";
				}
			} else {
				$response->type = "warning";
				$response->error = "Usuário não existe!";
			}
		} else {
			$response->type = "error";
			$response->error = "Parâmetros incorretos";
		}
	} else {
		$response->error = "Dados obrigatórios não informados";
	}
}

echo json_encode($response);