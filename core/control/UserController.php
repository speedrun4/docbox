<?php
namespace Docbox\control;

include_once (dirname(__FILE__) . "/Controller.php");
include_once (dirname(__FILE__) . "/../model/Modification.php");
include_once (dirname(__FILE__) . "/ModificationController.php");
include_once (dirname(__FILE__) . "/DepartmentController.php");
include_once (dirname ( __FILE__ ) . "/../model/User.php");
include_once (dirname ( __FILE__ ) . "/../model/KeyValue.php");

use DocBox\model\KeyValue;
use Docbox\model\Modification;
use Docbox\model\User;

class UserController extends Controller {
	function getUsers() {
		$users = array();
		$query = "SELECT * FROM users WHERE usr_dead = FALSE ORDER BY usr_name";
		if ($result = $this->db->query($query)) {
			while ($row = $result->fetch_object()) {
				$users[] = new KeyValue($row->usr_id, utf8_encode($row->usr_name));
			}
		}
		return $users;
	}

	/**
	 * @param User $user
	 * @return number
	 */
	function registerUser($user_id, $user) {
		$id = 0;
		$ok = FALSE;
		$this->db->begin();

		$query = "INSERT INTO users(usr_client, usr_profile, usr_name, usr_login, usr_pass, usr_email) VALUES(?,?,?,?,?,?)";
		if ($stmt = $this->db->prepare($query)) {
			if ($stmt->bind_param("iissss", $user->client, $user->profile, $user->name, $user->login, $user->password, $user->email)) {
				if ($stmt->execute()) {
					$id = $stmt->insert_id;
					if ($id != 0) {
						if (ModificationController::writeModification($this->db, "users", $id, "I", $user_id)) {
							$ok = TRUE;
						}
					}
				}
			}
		}

		if ($ok) {
			$this->db->commit();
		} else {
			$this->db->rollback();
			$id = 0;
		}

		return $id;
	}

	/**
	 * @param User $user
	 */
	function userExists($user) {
		$query = "SELECT * FROM users WHERE (usr_login like '" . $user->getLogin() . "' OR usr_email like '" .
			$user->getEmail() . "') AND usr_dead = FALSE";

		if ($result = $this->db->query($query)) {
			if ($result->fetch_object()) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getUserByLogin($login, $client) {
		$user = NULL;
		$query = "SELECT * FROM users WHERE usr_login like '$login' AND usr_client = $client";

		if ($result = $this->db->query($query)) {
			if ($row = $result->fetch_object()) {
				$user = User::withRow($row);
			}
		}

		return $user;
	}

	function getUserByEmail($email) {
		$user = NULL;
		$query = "SELECT * FROM users WHERE usr_email like '$email'";

		if ($result = $this->db->query($query)) {
			if ($row = $result->fetch_object()) {
				$user = User::withRow($row);
			}
		}

		return $user;
	}

	/**
	 *
	 * @param int $id
	 * @return User|NULL
	 */
	function getUserById($id) {
		$query = "select * FROM users where usr_id = $id";
		if ($result = $this->db->query($query)) {
			if ($row = $result->fetch_object()) {
				return User::withRow($row);
			}
		}
		return NULL;
	}

	/**
	 *
	 * @param User $user
	 */
	function updateUser($userLoggedId, $user) {
		$types = "sssii";
		$params = array(
			$user->name,
			$user->login,
			$user->email,
			$user->profile,
			$user->client
		);
		$query = "UPDATE users SET usr_name = ?, usr_login = ?, usr_email=?, usr_profile=?, usr_client=? ";

		if ($user->getPassword() != NULL && ! empty($user->getPassword())) {
			$types .= "s";
			$params[] = $user->password;
			$query .= ",usr_pass=? ";
		}

		if ($user->getPhoto() != NULL && ! empty($user->getPhoto())) {
			$types .= "s";
			$params[] = $user->photo;
			$query .= ",usr_photo=? ";
		}

		$types .= "i";
		$params[] = $user->id;
		$query .= "WHERE usr_id=?";

		if ($stmt = $this->db->prepare($query)) {
			if ($stmt->bind_param($types, ...$params)) {
				return $stmt->execute();
			}
		}

		return false;
	}

	/**
	 *
	 * @param integer $user
	 * @param string $pass
	 */
	function updateUserPass($user, $pass) {
		$query = "UPDATE users set usr_pass = '$pass' where usr_id = $user";
		if ($this->db->query($query)) {
			if (ModificationController::writeModification($this->db, "users", $user, Modification::UPDATEUSERPASS, $user)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * TODO Gravar modificação
	 *
	 * @param int $userLoggedId
	 * @param User $user
	 * @return boolean
	 */
	function updateUserPhoto($userLoggedId, $user) {
		$query = "UPDATE users set usr_photo = ? where usr_id = ?";
		if ($stmt = $this->db->prepare($query)) {
			if ($stmt->bind_param("si", $user->photo, $user->id)) {
				if ($stmt->execute()) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Usuário não pode ser administrador
	 * Usuário não pode ser o mesmo logado
	 * Usuário não pode ter pedidos não finalizados
	 *
	 * @param User $user
	 */
	function deleteUser($user, $userLoggedId) {
		$ok = false;
		$this->db->begin();
		if ($user->getId() != $userLoggedId) {
			$query = "update users set usr_dead = TRUE where usr_id = " . $user->getId();

			if ($this->db->query($query)) {
				if (ModificationController::writeModification($this->db, "usuarios", $user->getId(),
					Modification::DELETE, $userLoggedId)) {
					$ok = TRUE;
				}
			}
		}

		if ($ok) {
			$this->db->commit();
		} else {
			$this->db->rollback();
		}

		return $ok;
	}
	
	function getUserDepartments($id) {
		$departments = array();
		$query = "select * from usuario_departamentos WHERE usd_user = $id";
		if($result = $this->db->query($query)) {
			$depController = new DepartmentController($this->db);
			while($row = $result->fetch_object()) {
				$departments[] = $depController->getDepartmentById($row->usd_department);
			}
		}
		return $departments;
	}
	
	function getUserDepartmentIDs($id) {
		$departments = array();
		$query = "select * from usuario_departamentos WHERE usd_user = $id";
		if($result = $this->db->query($query)) {
			$depController = new DepartmentController($this->db);
			while($row = $result->fetch_object()) {
				$departments[] = $row->usd_department;
			}
		}
		return $departments;
	}

	/**
	 *
	 * @param integer $user
	 * @param integer[] $departments
	 */
	function setUserDepartments($user, $departments) {
		$query = "DELETE FROM usuario_departamentos WHERE usd_user = $user";
		if ($this->db->query ( $query )) {
			foreach ( $departments as $department ) {
				$query = "INSERT INTO usuario_departamentos(usd_user, usd_department) VALUES(?,?)";
				if ($stmt = $this->db->prepare ( $query )) {
					if ($stmt->bind_param ( "ii", $user, $department )) {
						if($stmt->execute()) {
							if($stmt->affected_rows == 0) {
								return FALSE;
							}
						} else {
							return FALSE;
						}
					}
				}
			}
		}

		$this->db->commit();
		return TRUE;
	}
}