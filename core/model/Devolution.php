<?php
namespace Docbox\model;

include_once (dirname ( __FILE__ ) . "/../model/User.php");
include_once (dirname ( __FILE__ ) . "/../model/Request.php");

use DateTime;

class Devolution {
	/**
	 *
	 * @var int
	 */
	var $id;
	/**
	 *
	 * @var int
	 */
	var $number;
	/**
	 *
	 * @var \DateTime
	 */
	var $datetime;
	/**
	 *
	 * @var string
	 */
	var $file;
	/**
	 * @var User
	 */
	var $user;
	/**
	 * 
	 * @var int
	 */
	var $reqType;
	
	static public function withRow($row) {
		$instance = new self ();

		$instance->id = $row->ret_id;
		$instance->reqType = $row->ret_req_type;
		$instance->user = User::withRow($row);
		$instance->number = $row->ret_number;
		$instance->file = $row->ret_file;
		$instance->setDatetime ( DateTime::createFromFormat ( "Y-m-d H:i:s", $row->ret_creation_time ) );
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
	 * @return number
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * @return number
	 */
	public function getReqType() {
		return $this->reqType;
	}

	/**
	 * @param number $reqType
	 */
	public function setReqType($reqType) {
		$this->reqType = $reqType;
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
	 * @return string
	 */
	public function getFile() {
		return $this->file;
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
	 * @param number $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}

	/**
	 * @return \Docbox\model\User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @param \Docbox\model\User $user
	 */
	public function setUser($user) {
		$this->user = $user;
	}

	/**
	 * @return number
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param number $type
	 */
	public function setType($type) {
		$this->type = $type;
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
	 * @param string $file
	 */
	public function setFile($file) {
		$this->file = $file;
	}
}