<?php
namespace Docbox\model;

class Department {
	var $id;
	var $client;
	var $name;
	public static function withRow($row) {
		$instance = new self ();
		$instance->id = $row->dep_id;
		$instance->client = $row->dep_client;
		$instance->name = utf8_encode ( $row->dep_name );
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
	public function getClient() {
		return $this->client;
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
	 * @param mixed $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 *
	 * @param mixed $client
	 */
	public function setClient($client) {
		$this->client = $client;
	}

	/**
	 *
	 * @param mixed $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
}