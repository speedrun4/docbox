<?php
namespace Docbox\model;

use mysqli_result;

class DocumentType {
	const PREFFIX_LENGTH = 3;
	/**
	 * @var integer
	 */
	var $id;
	/**
	 * @var string
	 */
	var $description;
	/**
	 * @var string
	 */
	var $preffix;
	/**
	 * 
	 * @param mysqli_result $row
	 */
	static public function withRow($row) {
		$instance = new self();
		$instance->id = $row->dct_id;
		$instance->description = $row->dct_name;
		$instance->preffix = $row->dct_preffix;

		return $instance;
	}
	/**
	 * @return number
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param number $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	/**
	 * @return string
	 */
	public function getPreffix() {
		return $this->preffix;
	}

	/**
	 * @param string $preffix
	 */
	public function setPreffix($preffix) {
		$this->preffix = $preffix;
	}

}