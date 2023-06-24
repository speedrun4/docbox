<?php
namespace DocBox\model;
/**
 * @author ailton
 */
class KeyValue {
	/**
	 * @var integer
	 */
	var $id = NULL;
	/**
	 * @var string
	 */
	var $name = NULL;
	/**
	 * @param integer $id
	 * @param string $name
	 */
	public function __construct($id, $name) {
		$this->setId($id);
		$this->setName(($name));
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
	public function getName() {
		return $this->name;
	}
	/**
	 * @param number $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
}