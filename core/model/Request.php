<?php
namespace DocBox\model;

include_once (dirname ( __FILE__ ) . "/User.php");

use Docbox\model\User;
use mysqli_result;
use DateTime;
use stdClass;

class Request {
	/**
	 *
	 * @var integer
	 */
	var $id;
	/**
	 *
	 * @var integer RequestType
	 */
	var $type;
	/**
	 *
	 * @var integer
	 */
	var $number;
	/**
	 *
	 * @var DateTime
	 */
	var $datetime;
	/**
	 *
	 * @var Document[]
	 */
	var $documents;
	/**
	 *
	 * @var integer
	 */
	var $status;
	/**
	 *
	 * @var User
	 */
	var $user;
	/**
	 *
	 * @var int
	 */
	var $client;
	/**
	 *
	 * @param mysqli_result $row
	 * @return Request
	 */
	static public function withRow($row) {
		$instance = new self ();
		$instance->setId ( $row->req_id );
		$instance->setType ( $row->req_type );
		$instance->setStatus ( $row->req_status );
		$instance->setDatetime ( DateTime::createFromFormat ( "Y-m-d H:i:s", $row->req_datetime ) );
		$user = User::withRow ( $row );
		$instance->setUser ( $user );
		$instance->setNumber ( $row->req_number );
		$instance->setClient ( $row->req_client );
		return $instance;
	}
	/**
	 *
	 * @return number
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * @return DateTime
	 */
	public function getDatetime() {
		return $this->datetime;
	}

	/**
	 *
	 * @return multitype:Document
	 */
	public function getDocuments() {
		return $this->documents;
	}

	/**
	 *
	 * @return number
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 *
	 * @param number $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 *
	 * @param DateTime $datetime
	 */
	public function setDatetime($datetime) {
		$this->datetime = $datetime;
	}

	/**
	 *
	 * @param multitype:Document $documents
	 */
	public function setDocuments($documents) {
		$this->documents = $documents;
	}

	/**
	 *
	 * @param number $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}
	/**
	 *
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 *
	 * @param stdClass $user
	 */
	public function setUser($user) {
		$this->user = $user;
	}
	/**
	 *
	 * @return number
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 *
	 * @param number $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}
	/**
	 *
	 * @return int
	 */
	public function getClient() {
		return $this->client;
	}
	/**
	 *
	 * @param int $client
	 */
	public function setClient($client) {
		$this->client = $client;
	}
	/**
	 *
	 * @return integer
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @param integer $type
	 */
	public function setType($type) {
		$this->type = $type;
	}
}
