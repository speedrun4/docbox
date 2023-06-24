<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../model/User.php");
include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/UserController.php");
include_once (dirname ( __FILE__ ) . "/../control/DepartmentController.php");
include_once (dirname ( __FILE__ ) . "/../control/ModificationController.php");
include_once (dirname ( __FILE__ ) . "/../model/User.php");

use Docbox\control\DepartmentController;
use Docbox\control\UserController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;
use function Docbox\utils\createToken;
use function Docbox\utils\getReqParam;
use function Docbox\utils\validatePassword;
use DateTime;
use stdClass;

$userLogged = getUserLogged();
if ($userLogged == NULL || $userLogged->getClient() == NULL || $userLogged->getClient () <= 0) {
	exit();
}

$response = new stdClass();
$response->ok = false;
$response->type = "error";

if (isset($_GET['files'])) {
	$allowedExtensions = array("jpg", "jpeg", "gif", "png");
	$files = array ();

	// Cria o token do upload
	$token = sha1("pn" . rand(1, 32768) . "" . (new DateTime("now"))->getTimestamp());
	$uploaddir = dirname(__FILE__) . "/../../photos/";
	if (! is_dir($uploaddir))
		mkdir($uploaddir, 0755, true);

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

	echo json_encode($response);
} else {
	$profile = getReqParam("userprofile", "int", "post");
	$name = getReqParam("username", "str", "post");
	$login = getReqParam("login", "str", "post");
	$password = getReqParam("password", "str", "post");
	$cpassword = getReqParam("confpassword", "str", "post");
	$email = getReqParam("email", "str", "post");
	$token = getReqParam("user_token", "str", "post");

	$db = new DbConnection();

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
			$response->error = "Dados inconsistentes";
			echo json_encode($response);
			exit;
		}
		
	}

	if(!empty($password) && !empty($cpassword)) {
		if(mb_strlen($password) < 64 && strcmp($password, $cpassword) == 0) {
			$hasPassword = validatePassword($password);
			if($hasPassword) $password = sha1($password);
		}
	}

	if(empty($token)) $token = NULL;

	if ($profile >= User::USER_ADMIN && $profile <= User::USER_COMMON && !empty($name) && !empty($login) && !empty($password) && !empty($cpassword) && $hasPassword) {
		if(mb_strlen($name) < 64 && mb_strlen($name) < 64 && mb_strlen($login) < 64 && mb_strlen($password) < 64) {
			$userController = new UserController($db);

			$user = new User();
			$user->setName($name);
			$user->setLogin($login);
			$user->setProfile($profile);
			$user->setPassword($password);
			$user->setPhoto($token);
			$user->setEmail($email);
			if ($profile == User::USER_COMMON) {
				$user->setClient($userLogged->getClient());
			} else if($profile == User::USER_ADMIN) {
				$user->setClient(NULL);
			}

			if (!$userController->userExists($user)) {
				$id = $userController->registerUser($userLogged->getId(), $user);
				if ($id > 0) {
				    $user->setId($id);

				    if ($profile == User::USER_COMMON) {
						// Grava os departamentos do usuário
					    $userController->setUserDepartments($user->getId(), $departments);
				    }

				    if($token != NULL) {
    				    // Renomeia o arquivo de foto
    				    $uploaddir = dirname ( __FILE__ ) . "/../../photos/";
    				    $newToken = createToken($id) . ".jpg";

    				    if(rename($uploaddir . $token, $uploaddir . $newToken)) {
    				        $user->setPhoto($newToken);
        				    // Seta no DB o nome da foto
    				        if($userController->updateUserPhoto($userLogged->getId(), $user)) {
            					$response->ok = TRUE;
            					$response->type = "success";
    				        } else {
    				        	$response->ok = TRUE;
    				            $response->type = "warning";
    				            $response->error = "O usuário foi criado!, Porém não foi possível atualizar a foto do usuário";
    				        }
    				    } else {
    				        $response->type = "warning";
    				        $response->error = "Não foi possível atualizar a foto do usuário";
    				    }
				    } else {
				        $response->ok = TRUE;
				        $response->type = "success";
				    }
				} else {
				    $response->type = "error";
				    $response->error = "Erro ao cadastrar usuário";
				}
			} else {
				$response->type = "warning";
				$response->error = "Login ou email já cadastrado!";
			}
		} else {
		    $response->type = "error";
		    $response->error = "Dados inconsistentes";
		}
	} else {
	    $response->type = "error";
		$response->error = "Dados obrigatórios não informados";
	}
	echo json_encode($response);
}