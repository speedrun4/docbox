<?php
namespace Docbox\model;

class Client {
	var $id;
	var $name;
	var $labelText;
	public static function withRow($row) {
		$instance = new self ();
		$instance->setId ( $row->cli_id );
		$instance->setName ( $row->cli_name );
		$instance->setLabelText ( $row->cli_label_name );
		return $instance;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getLabelText() {
		return $this->labelText;
	}

	/**
	 *
	 * @param mixed $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 *
	 * @param mixed $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 *
	 * @param mixed $labelText
	 */
	public function setLabelText($labelText) {
		$this->labelText = $labelText;
	}
}