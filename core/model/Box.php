<?php
namespace Docbox\model;

use mysqli_result;

include_once (dirname(__FILE__) . "/Request.php");
include_once (dirname(__FILE__) . "/Department.php");

class Box {
	var $id;
	/**
	 * @var int
	 */
	var $number;
	/**
	 * @var int
	 */
	var $client;
	/**
	 * @var string
	 */
	var $corridor;
	/**
	 * @var int
	 */
	vaR $tower;
	/**
	 * @var int
	 */
	var $floor;
	/**
	 * @var Department
	 */
	var $department;
	/**
	 * 
	 * @var boolean
	 */
	var $sealed;
	/**
	 * 
	 * @var NULL|Request
	 */
	var $request;
	/**
	 * @param mysqli_result $row
	 * @return Box
	 */
	static function withRow($row) {
		$instance = new self();

		$instance->id = $row->box_id;
		$instance->number = $row->box_number;
		$instance->client = $row->box_client;
		$instance->corridor = $row->box_corridor;
		$instance->tower = $row->box_tower;
		$instance->floor = $row->box_tower;
		$instance->department = Department::withRow($row);
		if($row->box_request > 0) $instance->request = Request::withRow($row);
		$instance->sealed = boolval($row->box_sealed);

		return $instance;
	}
	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}
	/**
	 * @param mixed $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return number
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * @return number
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * @return string
	 */
	public function getCorridor() {
		return $this->corridor;
	}

	/**
	 * @return number
	 */
	public function getTower() {
		return $this->tower;
	}

	/**
	 * @return number
	 */
	public function getFloor() {
		return $this->floor;
	}

	/**
	 * @param number $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}

	/**
	 * @param number $client
	 */
	public function setClient($client) {
		$this->client = $client;
	}

	/**
	 * @param string $corridor
	 */
	public function setCorridor($corridor) {
		$this->corridor = $corridor;
	}

	/**
	 * @param number $tower
	 */
	public function setTower($tower) {
		$this->tower = $tower;
	}

	/**
	 * @param number $floor
	 */
	public function setFloor($floor) {
		$this->floor = $floor;
	}
    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }
    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }
    /**
     * @return boolean
     */
    public function isSealed()
    {
        return $this->sealed;
    }

    /**
     * @param boolean $sealed
     */
    public function setSealed($sealed)
    {
        $this->sealed = $sealed;
    }
}