<?php
include_once (dirname(__FILE__) . "/../model/DbConnection.php");
include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../utils/Input.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");

use function Docbox\control\createUserSession;
use Docbox\model\DbConnection;
use Docbox\utils\Input;

$response = new stdClass();
$response->ok = FALSE;

$user = trim(Input::str('user'));
$pass = Input::str('pass');

if (! empty($user) && ! empty($pass)) {
    $db = new DbConnection();
    $response->ok = createUserSession($db, $user, $pass);
}

echo json_encode($response);