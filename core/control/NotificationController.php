<?php
namespace Docbox\control;

include_once (dirname(__FILE__) . "/Controller.php");
include_once (dirname(__FILE__) . "/../model/Notification.php");

use DocBox\model\Notification;

class NotificationController extends Controller {
	/**
	 * Return the last 10 notifications from db
	 */
	function getLastNotifications($user_requested) {
		$notifications = array();
		
 		$query = "SELECT *, (SELECT group_concat(nov_user) FROM notifications_viewers WHERE nov_notification = not_id) AS viewers 
		FROM notifications 
		LEFT JOIN users ON usr_id = not_user 
		LEFT JOIN clientes ON usr_client = cli_id
		ORDER BY not_when DESC LIMIT 0, 10";

 		if($result = $this->db->query($query)) {
 			while($row = $result->fetch_object()) {
 				$notification = Notification::withRow($row);
 				$notifications []= $notification;
 			}
 		}
 		return $notifications;
	}

	/**
	 * 
	 * @param integer $user_id
	 * @param integer $tb_id
	 * @param integer $type
	 * @param integer $event
	 * @return boolean
	 */
	function registerNotification($user_id, $tb_id, $type, $event) {
		$query = "INSERT INTO notifications(not_user, not_when, not_tb_id, not_type, not_event) VALUES (?,CURRENT_TIMESTAMP,?,?,?)";
		if($stmt = $this->db->prepare($query)) {
			if($stmt->bind_param("iiii", $user_id, $tb_id, $type, $event)) {
				$stmt->execute();
				$id = $stmt->insert_id;
				return $id > 0;
			}
		}
	}

	function setUserViewed($user_id, $notification_id) {
		$query = "REPLACE INTO notifications_viewers(nov_notification, nov_user) VALUES($notification_id, $user_id)";
		return $this->db->query($query);
	}
	
	function muteNotification($type, $table_id) {
		$ok = FALSE;
		$query = "UPDATE notifications SET not_alert = FALSE WHERE not_type = $type AND not_tb_id = $table_id";
		if($stmt = $this->db->prepare($query)) {
			if($stmt->execute()) {
				$ok = $stmt->affected_rows > 0;
			}
		}

		return $ok;
	}
	
	function muteNotificationById($id) {
		$query = "UPDATE notifications SET not_alert = FALSE WHERE not_id = $id";
		if($stmt = $this->db->prepare($query)) {
			if($stmt->execute()) {
				return $stmt->affected_rows > 0;
			}
		}
		return FALSE;
	}
}