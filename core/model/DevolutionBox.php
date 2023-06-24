<?php
namespace Docbox\model;

include_once (dirname ( __FILE__ ) . "/Box.php");
include_once (dirname ( __FILE__ ) . "/Request.php");

class DevolutionBox {
    var $returnID;
    var $requestId;
    var $requestNumber;

    /**
     * @var Box
     */
    var $box;

    static function withRow($row) {
    	$obj = new self();
    	$obj->returnID = $row->bre_return;
    	$obj->requestId =$row->req_id;
    	$obj->requestNumber =$row->req_number;
    	$obj->box = Box::withRow($row);
    	return $obj;
    }
}