<?php
namespace Docbox\control;

include_once (dirname ( __FILE__ ) . "/Controller.php");
include_once (dirname ( __FILE__ ) . "/../model/User.php");
include_once (dirname ( __FILE__ ) . "/../model/Request.php");
include_once (dirname ( __FILE__ ) . "/../model/RequestStatus.php");

use Docbox\model\RequestStatus;
use Docbox\model\User;
use DateTime;
use stdClass;

class StatisticsController extends Controller {
    private function getTotalPerStatus($cli_id, $status) {
        $total = 0;
        $query = "SELECT count(*) as total FROM pedidos WHERE req_client = $cli_id AND req_status = $status";
        if($result = $this->db->query($query)) {
            if($row = $result->fetch_object()) {
                $total = $row->total;
            }
        }
        return $total;
    }

    function getTotalOpenedRequests($cli_id) {
        return $this->getTotalPerStatus($cli_id, RequestStatus::OPENED);
    }

    function getTotalReturnedRequests($cli_id) {
        return $this->getTotalPerStatus($cli_id, RequestStatus::RETURNED);
    }
    
    function getTotalOpenedDevolutions() {
    	$total = 0;
    	$query = "select count(*) as total
				FROM devolucoes 
				WHERE ret_file is NULL";
    	if($result = $this->db->query($query)) {
    		if($row = $result->fetch_object()) {
    			$total = $row->total;
    		}
    	}
    	return $total;
    }

    function getTotalSentRequests($cli_id) {
        return $this->getTotalPerStatus($cli_id, RequestStatus::SENT);
    }

    function getTotalAttendedRequests($cli_id) {
        return $this->getTotalPerStatus($cli_id, RequestStatus::ATTENDEND);
    }
    
    function getTotalCompletedRequests($cli_id) {
        return $this->getTotalPerStatus($cli_id, RequestStatus::COMPLETED);
    }

    function getTotalRequestsPerMonth($cli_id, $month) {
        $total = 0;
        $query = "SELECT count(*) AS total from pedidos WHERE req_client = $cli_id AND month(req_datetime) = $month";
        if($result = $this->db->query($query)) {
            if($row = $result->fetch_object()) {
                $total = $row->total;
            }
        }
        return $total;
    }

    function getCancelledRequestsPercent($cli_id) {
        $total = 0;
        $query = "SELECT count(*)/(select count(*) from pedidos WHERE req_client = $cli_id) as total FROM pedidos WHERE req_client = $cli_id AND req_status = " . RequestStatus::CANCELED;
        if($result = $this->db->query($query)) {
            if($row = $result->fetch_object()) {
                $total = $row->total;
            }
        }
        return $total;
    }

    function getTotalRequestsPerYear($cli_id, $status, $year) {
    	$data = array(0,0,0,0,0,0,0,0,0,0,0,0);
        if($status == NULL) {
        	$statusWhere = "and req_status <> " . RequestStatus::CANCELED;
        } else {
        	$statusWhere = "and req_status = $status";
        }
        $query = "SELECT month(req_datetime) as month, count(req_id) as count FROM pedidos where req_client = $cli_id $statusWhere". " AND year(req_datetime) = $year GROUP BY month(req_datetime)";
        if($result = $this->db->query($query)) {
            while($row = $result->fetch_object()) {
                $data[$row->month - 1] = $row->count;
            }
        }
        return $data;
    }

    function getYearsContainsRequests() {
    	$years = array();
    	$query = "select distinct year(req_datetime) as year from pedidos order by year desc";
    	if($result = $this->db->query($query)) {
    		while($row = $result->fetch_object()) {
    			$years[] = $row->year;
    		}
    	}
    	return $years;
    }

    function getPerformanceChart($action, $date_from, $date_to) {
        $query = "select usr_name, count(*) as count from modificacoes INNER JOIN users ON usr_id = mod_user ";
        
        $where = " WHERE usr_profile = " . User::USER_ADMIN;
        if(!empty($action)) {
            $where .= " AND mod_action like '$action'";
        }
        if(!empty($date_from)) {
            if($date_from = DateTime::createFromFormat("Y-m-d", $date_from)) {
                $where .= " AND ";
                if(!empty($date_to)) {
                    if($date_to = DateTime::createFromFormat("Y-m-d", $date_to)) {
                        $where .= "date(mod_when) BETWEEN '" . $date_from->format('Y-m-d') . "' AND '" . $date_to->format('Y-m-d') . "'";
                    }
                } else {
                    $where .= "date(mod_when) = '" . $date_from->format('Y-m-d') . "' ";
                }
            }
        } else if(!empty($date_to)) {
            if($date_to = DateTime::createFromFormat("Y-m-d", $date_to)) {
                $where .= " AND date(mod_when) <= '" . $date_to->format('Y-m-d') . "'";
            }
            
        }
        $query .= $where;
        $query .= " group by mod_user ORDER BY usr_name ";
        // echo $query;
        $labels = array();
        $series = array();
        if($result = $this->db->query($query)) {
            while($row = $result->fetch_object()) {
                $labels []= utf8_encode($row->usr_name);
                $series []= $row->count;
            }
        }
        $obj = new stdClass();
        $obj->labels = $labels;
        $obj->series = $series;
        return $obj;
    }
    
    function getBoxRegisteredPerformance($date_from, $date_to) {
        $where = "";
        if(!empty($date_from)) {
            if($date_from = DateTime::createFromFormat("Y-m-d", $date_from)) {
                $where .= " AND ";
                if(!empty($date_to)) {
                    if($date_to = DateTime::createFromFormat("Y-m-d", $date_to)) {
                        $where .= "date(mod_when) BETWEEN '" . $date_from->format('Y-m-d') . "' AND '" . $date_to->format('Y-m-d') . "'";
                    }
                } else {
                    $where .= "date(mod_when) = '" . $date_from->format('Y-m-d') . "' ";
                }
            }
        } else if(!empty($date_to)) {
            if($date_to = DateTime::createFromFormat("Y-m-d", $date_to)) {
                $where .= " AND date(mod_when) <= '" . $date_to->format('Y-m-d') . "'";
            }
        }
        $query = "SELECT mod_user, usr_name, count(*) AS count
        FROM modificacoes
        INNER JOIN users on usr_id = mod_user
        INNER JOIN caixas ON box_id = mod_tb_id
        WHERE mod_table like 'caixas' and mod_action like 'I' and box_dead = false $where 
        GROUP by mod_user 
        ORDER BY usr_name DESC;";

        $labels = array();
        $series = array();
        if($result = $this->db->query($query)) {
            while($row = $result->fetch_object()) {
                $labels []= utf8_encode($row->usr_name);
                $series []= $row->count;
            }
        }
        $obj = new stdClass();
        $obj->labels = $labels;
        $obj->series = $series;
        return $obj;
    }

    /**
     * Retorna a média de cadastros diários de cada usuário
     * 
     * @return number[][]|NULL[][]
     */
    function getAverageUserDocsByDay() {
        $data = array();
        $query = "SELECT usr_name, count(mod_action) as num_actions, count(distinct date(mod_when)) as num_days FROM modificacoes
            INNER JOIN users ON mod_user = usr_id 
            WHERE (mod_table LIKE 'documentos' OR mod_table LIKE 'livros') 
            	AND mod_action LIKE 'I' 
            GROUP BY mod_user 
            ORDER BY usr_name;";
        if($result = $this->db->query($query)) {
            while($row = $result->fetch_object()) {
                $data[] = array($row->usr_name, $row->num_actions, $row->num_days, round($row->num_actions / $row->num_days, 2));
            }
        }
        return $data;
    }
}