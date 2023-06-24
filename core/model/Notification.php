<?php
namespace DocBox\model;

class Notification {
	var $id;
	var $username;
	var $type;
	var $client;
	var $datetime;
	var $viewers;
	/**
	* @var integer
	*/
	var $event;
	/**
	 * ID do objeto da notificação
	 * @var integer
	 */
	var $objectId;
	var $shouldAlert;

	static function withRow($row) {
		$instance = new self ();

		$instance->id = $row->not_id;
		$instance->username = utf8_encode($row->usr_name);
		$instance->userphoto = $row->usr_photo;
		$instance->datetime = \DateTime::createFromFormat ( "Y-m-d H:i:s", $row->not_when )->format("d/m/Y à\s H:i");
		$instance->type = $row->not_type;
		$instance->event = $row->not_event;
		$instance->client = utf8_encode($row->cli_name);
		$instance->objectId = $row->not_tb_id;
		$instance->viewers = explode(",", $row->viewers);
		$instance->shouldAlert = $row->not_alert;

		return $instance;
	}
	/**
	 * @return mixed
	 */
	public function getEvent() {
		return $this->event;
	}

	/**
	 * @param mixed $event
	 */
	public function setEvent($event) {
		$this->event = $event;
	}

}

class NotificationType {
	const REQUEST = 1;
	const DEVOLUTION = 2;
	const WITHDRAWAL = 3;
}

class NotificationEvent {
	const REGISTER = 1;
	const CANCEL = 2;
}