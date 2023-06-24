<?php

namespace Docbox\model;

class WithdrawalDoc {
	/**
	 * @var integer
	 */
	var $id;
	/**
	 *
	 * @var int
	 */
	var $keyWithdrawal;
	/**
	 *
	 * @var int
	 */
	var $number;
	/**
	 *
	 * @var int
	 */
	var $year;
	public function __construct($number, $year, $withdrawalID = NULL) {
		$this->setNumber ( $number );
		$this->setYear ( $year );
		$this->setIdWithdrawal ( $withdrawalID != NULL ? $withdrawalID : 0 );
	}
	public static function withRow($row) {
		$instance = new self($row->pud_number, $row->pud_year, $row->pud_id_withdrawal);
		$instance->setId($row->pud_id);
		return $instance;
	}
	public function getId() {
		return $this->id;
	}
	public function setId($id) {
		$this->id = $id;
	}
	/**
	 *
	 * @return number
	 */
	public function getIdWithdrawal() {
		return $this->keyWithdrawal;
	}

	/**
	 *
	 * @param number $id
	 */
	public function setIdWithdrawal($id) {
		$this->keyWithdrawal = $id;
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
	 * @return number
	 */
	public function getYear() {
		return $this->year;
	}

	/**
	 *
	 * @param number $year
	 */
	public function setYear($year) {
		$this->year = $year;
	}
}
