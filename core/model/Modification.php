<?php
namespace Docbox\model;

include_once (dirname ( __FILE__ ) . "/RequestStatus.php");

class Modification {
	const INSERT = "I";
	const UPDATE = "U";
	const DELETE = "D";
	const CANCEL = "C";
	const UPDATE2SENT = 'U2' . RequestStatus::SENT;
	const UPDATE2ATTENDEND = 'U2' . RequestStatus::ATTENDEND;
	const UPDATE2CANCELED = 'U2' . RequestStatus::CANCELED;
	const UPDATE2COMPLETED = 'U2' . RequestStatus::COMPLETED;
	const UPDATE2RETURNED = 'U2' . RequestStatus::RETURNED;
	const UPDATE2RETURNING = 'U2' . RequestStatus::RETURNING;
	const UPDATEUSERPASS = 'UUP';
	const UPDATE_DOCFILE = 'UF';
	const DELETE_DOCFILE = 'DF';
	const TRANSFER_ONEDOC = 'T1D';// Documento selecionado
	const TRANSFER_DOCS2BOX = 'T2B';// Todos de uma caixa para outra
	// const FREE_DOC_FROM_REQUEST = 'FD';
	const FINISH_DEVOLUTION = "FD";
	const DELETE_DEVOLUTION_FILE = "DDF";
	const UPDATE_DEVOLUTION_FILE = "UDF";
	const UPDATE_RECEIPT = "UWR";
	
	public static function listRequestModificationActions() {
		$actions = array(
				self::INSERT,
				self::UPDATE2SENT,
				self::UPDATE2ATTENDEND,
				self::UPDATE2RETURNED,
				self::UPDATE2RETURNING,
				self::UPDATE2COMPLETED
		);
		return $actions;
	}
}