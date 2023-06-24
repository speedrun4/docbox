<?php
namespace Docbox\control;

include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../model/User.php");
include_once (dirname ( __FILE__ ) . "/../control/UserController.php");

use Docbox\model\DbConnection;
use Docbox\model\User;

session_start();

$req_time = $_SERVER['REQUEST_TIME'];

/**
 * timeout, specified in seconds
 */
const MINUTES_IN_SECONDS = 60;
const HOUR = MINUTES_IN_SECONDS * 60;
$timeout_duration = 180 * MINUTES_IN_SECONDS;

/**
 * Here we look for the user's LAST_ACTIVITY timestamp. If
 * it's set and indicates our $timeout_duration has passed,
 * kicks the user away
 */
if (isset($_SESSION['LAST_ACTIVITY']) && ($req_time - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
}

/**
 * Finally, update LAST_ACTIVITY so that our timeout
 * is based on it and not the user's login time.
 */
$_SESSION['LAST_ACTIVITY'] = $req_time;

/**
 * @param DbConnection $db
 * @param string $login
 * @param string $password
 * @return boolean
 */
function createUserSession($db, $login, $password) {
	if(!empty($login) && !empty($password)) {
		$password = sha1($password);
		$query = "SELECT * FROM users where usr_dead=false and usr_login LIKE '$login' and usr_pass LIKE '$password' and usr_dead = false and usr_profile = 1";// somente adm loga
		if($result = $db->con->query($query)) {
			if ($row = $result->fetch_object()) {
				$_SESSION['usr_id'] = $row->usr_id;
				$_SESSION['usr_login'] = $row->usr_login;
				$_SESSION['usr_name'] = $row->usr_name;
				$_SESSION['usr_profile'] = $row->usr_profile;
				$_SESSION['usr_client'] = $row->usr_client;
				$_SESSION['usr_photo'] = $row->usr_photo;
				$_SESSION['timeout'] = 1000;

				// Atualiza o valor do último login
				$query = "UPDATE users SET usr_last_login = CURRENT_TIMESTAMP WHERE usr_id = " . $row->usr_id;
				$db->con->query($query);

				// Pega os departamentos do usuário
				$userController = new UserController($db);
				$_SESSION['usr_departments'] = $userController->getUserDepartmentIDs($row->usr_id);

				return TRUE;
			}
		}
	}

	return FALSE;
}

/**
 * @return NULL|Docbox\model\User
 */
function getUserLogged() {
	return User::withSessionArray();
}
/**
 * Modifica o cliente ao qual o usuário está trabalhando
 */
function setUserClient($client) {
	$_SESSION['usr_client'] = $client;
}

function doLogout() {
	session_destroy();
}
?>