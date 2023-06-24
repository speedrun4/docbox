<?php
namespace Docbox\control;

include_once (dirname ( __FILE__ ) . "/Controller.php");
include_once (dirname ( __FILE__ ) . "/../model/User.php");
include_once (dirname ( __FILE__ ) . "/../model/Department.php");
include_once (dirname ( __FILE__ ) . "/../model/DocumentType.php");
include_once (dirname ( __FILE__ ) . "/ModificationController.php");

use Docbox\model\Department;
use Docbox\model\DocumentType;
use Docbox\model\User;

class DepartmentController extends Controller {
    /**
     * @param Department $dep_name
     * @param User $user
     * @return boolean
     */
    public function registerDepartment($dep_name, $user) {
        $ok = false;
        $this->db->begin();

        $query = "INSERT INTO departamentos(dep_client, dep_name) VALUES(?,?)";

        if($stmt = $this->db->prepare($query)) {
            if($stmt->bind_param("is", $user->client, $dep_name)) {
                $stmt->execute();
                $id = $stmt->insert_id;
                $ok = $id > 0 && ModificationController::writeModification($this->db, "departamentos", $id, "I", $user->client);
            }
        }

        if($ok) {
            $this->db->commit();
        } else {
            $this->db->rollback();
        }

        return $ok;
    }

    /**
     * @param DocumentType $type
     */
    public function updateDepartment($id, $name, $user_id) {
        $ok = FALSE;
        $this->db->begin();
        $query = "UPDATE departamentos SET dep_name=? WHERE dep_id = ? and dep_dead=FALSE";
        if($stmt = $this->db->prepare($query)) {
            if($stmt->bind_param("si", $name, $id)) {
                $ok = $stmt->execute() && ModificationController::writeModification($this->db, "departamentos", $id, "U", $user_id);
            }
        }
        
        if($ok) {
            $this->db->commit();
        } else {
            $this->db->rollback();
        }
        
        return $ok;
    }

    /**
     * @param integer $id
     * @return boolean
     */
    public function deleteDepartment($id, $user_id) {
        $this->db->begin();
        $query = "UPDATE departamentos SET dep_dead = TRUE WHERE dep_id=?";

        if($stmt = $this->db->prepare($query)) {
            if($stmt->bind_param("i", $id)) {
                $ok = $stmt->execute() && ModificationController::writeModification($this->db, "departamentos", $id, "D", $user_id);
            }
        }

        if($ok) {
            $this->db->commit();
        } else {
            $this->db->rollback();
        }

        return $ok;
    }

    public function getDepartments($client) {
        $departments = array();
        $query = "SELECT * FROM departamentos WHERE dep_client = $client AND dep_dead = FALSE ORDER BY dep_name ASC";
        if($result = $this->db->query($query)) {
            while($row = $result->fetch_object()) {
                $departments[] = Department::withRow($row);
            }
        }
        return $departments;
    }

    public function getDepartmentById($id) {
        $type = NULL;
        $query = "SELECT * FROM departamentos WHERE dep_id= $id AND dep_dead = FALSE";

        if($result = $this->db->query($query)) {
            if($row = $result->fetch_object()) {
                $type = Department::withRow($row);
            }
        }

        return $type;
    }

    public function departmentExists($name, $client_id) {
        $num_rows = 0;
        $query = "SELECT * FROM departamentos WHERE dep_name like ? AND dep_client = ? AND dep_dead = FALSE";

        if($stmt = $this->db->prepare($query)) {
            if($stmt->bind_param("si", $name, $client_id)) {
                /* execute query */
                $stmt->execute();
                
                /* store result */
                $stmt->store_result();
                
                $num_rows = $stmt->num_rows;
                
                /* free result */
                $stmt->free_result();
                
                /* close statement */
                $stmt->close();
            }
        }
        return $num_rows;
    }
}