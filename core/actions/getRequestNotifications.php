<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/ClientController.php");
include_once (dirname ( __FILE__ ) . "/../control/NotificationController.php");

use Docbox\control\NotificationController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use stdClass;

$res = new stdClass ();
$res->notifications = array ();
$res->alertUser = FALSE;
$res->unseen = 0;

$userLogged = getUserLogged ();

if ($userLogged != NULL) {
	$db = new DbConnection ();
	$notificationController = new NotificationController ( $db );

	$res->notifications = $notificationController->getLastNotifications ( $userLogged->getId () );

	/**
	 * Se alguma dessas notificações é nova para o usuário ele será alertado
	 */
	foreach ( $res->notifications as $notification ) {
		$notification->seen = TRUE;
		if (! in_array ( $userLogged->getId (), $notification->viewers ) && $notification->shouldAlert) {
			$res->alertUser = $notification->shouldAlert;
			$res->unseen ++;
			$notification->seen = FALSE;
		}
		// Remove do objeto o array viewers, o cliente não precisa dessa informação
		unset ( $notification->viewers );
	}
}

echo json_encode ( $res );