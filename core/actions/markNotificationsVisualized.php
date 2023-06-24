<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/ClientController.php");
include_once (dirname ( __FILE__ ) . "/../control/NotificationController.php");
include_once (dirname ( __FILE__ ) . "/../utils/Input.php");

use Docbox\control\NotificationController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;

$userLogged = getUserLogged ();

$response = new \stdClass();
$response->ok = FALSE;

if ($userLogged != NULL) {
	$db = new DbConnection ();
	$notificationController = new NotificationController ( $db );

	$ids = $_GET['ids'];

	if(!empty($ids)) {
		$ids = explode(",", $_GET['ids']);
		$response->ok = TRUE;

		foreach ($ids as $notificationID) {
			if(! $notificationController->setUserViewed($userLogged->getId(), $notificationID)) {
				$response->ok = FALSE;
			} else {
				// $notificationController->muteNotificationById($notificationID);
			}
		}
	}
}

echo json_encode($response);