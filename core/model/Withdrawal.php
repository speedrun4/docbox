<?php
namespace Docbox\model;

use DateTime;

class Withdrawal {
	/**
	 * @var integer
	 */
	var $id;
	/**
	 * @var integer
	 */
	var $client;
		/**
	 * @var integer
	 */
	var $number;
	/**
	 * @var integer
	 */
	var $status;
	/**
	 *
	 * @var integer
	 */
	var $user_requested;
	/**
	 * @var \DateTime
	 */
	var $creationDate;
	/**
	 * @var \DateTime
	 */
	var $withdrawalDate;
	/**
	 * @var string
	 */
	var $receipt;

	public static function withRow($row) {
		$instance = new self ();
		$instance->id = $row->pul_id;
		$instance->client = $row->pul_client;
		$instance->number = $row->pul_number;
		$instance->status = $row->pul_status;
		$instance->user_requested = User::withRow($row);
		$instance->setCreationDate ( DateTime::createFromFormat ( "Y-m-d H:i:s", $row->pul_dt_creation ) );
		$instance->setWithdrawalDate ( DateTime::createFromFormat ( "Y-m-d H:i:s", $row->pul_dt_withdrawal ) );
		$instance->receipt = utf8_encode ( $row->pul_receipt );
		return $instance;
	}
	/**
	 * @return number
	 */
	public function getId() {
		return $this->id;
	}
	/**
	 * @param number $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	/**
	 * @return number
	 */
	public function getUserRequested() {
		return $this->user_requested;
	}
	/**
	 * @param number $user_requested
	 */
	public function setUserRequested($user_requested) {
		$this->user_requested = $user_requested;
	}
	/**
	 * @return DateTime
	 */
	public function getCreationDate() {
		return $this->creationDate;
	}
	/**
	 * @param DateTime $creationDate
	 */
	public function setCreationDate($creationDate) {
		$this->creationDate = $creationDate;
	}
	/**
	 * @return DateTime
	 */
	public function getWithdrawalDate() {
		return $this->withdrawalDate;
	}
	/**
	 * @param DateTime $withdrawalDate
	 */
	public function setWithdrawalDate($withdrawalDate) {
		$this->withdrawalDate = $withdrawalDate;
	}
	/**
	 * @return string
	 */
	public function getReceipt() {
		return $this->receipt;
	}

	/**
	 * @param string $receipt
	 */
	public function setReceipt($receipt) {
		$this->receipt = $receipt;
	}
	/**
	 * @return number
	 */
	public function getStatus() {
		return $this->status;
	}
	/**
	 * @param number $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}
	/**
	 * @return number
	 */
	public function getNumber() {
		return $this->number;
	}
	/**
	 * @param number $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}
	/**
	 * @return number
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * @param number $client
	 */
	public function setClient($client) {
		$this->client = $client;
	}

}