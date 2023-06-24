<?php
namespace Docbox\model;

include_once(dirname(__FILE__) . "/Department.php");

class User {
	const USER_ADMIN = 1;
	const USER_COMMON = 2;

	var $id;
	var $name;
	/** integer */
	var $client = 0;
	var $login;
	var $profile;
	var $password;
	var $photo;
	var $email;
	/**
	 * @var [] Department
	 */
	var $departments;

	public static function withRow($row) {
		$instance = new self();

		$instance->id = $row->usr_id;
		$instance->login = utf8_encode($row->usr_login);
		$instance->name = ($row->usr_name);
		$instance->profile = $row->usr_profile;
		$instance->client = $row->usr_client;
		$instance->photo = $row->usr_photo;
		$instance->email = utf8_encode($row->usr_email);

		return $instance;
	}

	public static function withSessionArray() {
		$instance = NULL;

		if(isset($_SESSION["usr_id"])) {
			$instance = new self();

			$instance->id = $_SESSION['usr_id'];
			$instance->login = $_SESSION['usr_login'];
			$instance->name = $_SESSION['usr_name'];
			$instance->profile = $_SESSION['usr_profile'];
			$instance->client = intval($_SESSION['usr_client']);
			$instance->photo = $_SESSION['usr_photo'];
			$instance->setDepartments($_SESSION['usr_departments']);
		}

		return $instance;
	}
	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}
	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}
	/**
	 * @return int
	 */
	public function getClient() {
		return $this->client;
	}
	/**
	 * @return mixed
	 */
	public function getLogin() {
		return $this->login;
	}
	/**
	 * @return mixed
	 */
	public function getProfile() {
		return $this->profile;
	}
	/**
	 * @param mixed $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	/**
	 * @param mixed $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
	/**
	 * @param mixed $client
	 */
	public function setClient($client) {
		$this->client = $client;
	}
	/**
	 * @param mixed $login
	 */
	public function setLogin($login) {
		$this->login = $login;
	}
	/**
	 * @param mixed $profile
	 */
	public function setProfile($profile) {
		$this->profile = $profile;
	}
	/**
	 * @return string
	 */
	public function getPhoto() {
		return $this->photo;
	}
	/**
	 * @param string $photo
	 */
	public function setPhoto($photo) {
		$this->photo = $photo;
	}
	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}
	/**
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}
	/**
	 * @return mixed
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param mixed $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}
	
	public function isAdmin() {
	    return $this->profile == User::USER_ADMIN;
	}
	/**
	 * @return []
	 */
	public function getDepartments() {
		return $this->departments;
	}

	/**
	 * @param [] $departments
	 */
	public function setDepartments($departments) {
		$this->departments = $departments;
	}

}