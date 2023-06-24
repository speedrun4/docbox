<?php
namespace Docbox\control;

include_once (dirname ( __FILE__ ) . "/Controller.php");
include_once (dirname ( __FILE__ ) . "/../model/Request.php");
include_once (dirname ( __FILE__ ) . "/../model/RequestType.php");
include_once (dirname ( __FILE__ ) . "/../model/RequestStatus.php");
include_once (dirname ( __FILE__ ) . "/../model/KeyValue.php");
include_once (dirname ( __FILE__ ) . "/../model/Document.php");
include_once (dirname ( __FILE__ ) . "/../model/User.php");
include_once (dirname ( __FILE__ ) . "/../model/RequestDocument.php");
include_once (dirname ( __FILE__ ) . "/../model/Modification.php");
include_once (dirname ( __FILE__ ) . "/ModificationController.php");
include_once (dirname ( __FILE__ ) . "/NotificationController.php");

use DocBox\model\KeyValue;
use DocBox\model\Request;
use Docbox\model\Modification;
use Docbox\model\RequestDocument;
use Docbox\model\RequestStatus;
use Docbox\model\RequestType;
use Docbox\model\User;
use DocBox\model\Notification;
use DocBox\model\NotificationType;
use DocBox\model\NotificationEvent;

class RequestController extends Controller {
	/**
	 * @param []int $boxes
	 * @param User $user
	 * @return number
	 */
	function registerBoxRequest($boxes, $user) {
		$ok = FALSE;
		$requestID = 0;
		if (is_array($boxes) && count($boxes) > 0)
		{
    		$this->db->begin ();
    		$number = $this->getNextRequestNumber ( $user->client );
    
    		$query = "INSERT INTO pedidos(req_type, req_number, req_status, req_user, req_client) VALUES(?,?,?,?,?)";
       		if ($number > 0 && $stmt = $this->db->prepare ( $query )) {
       		    $reqType = RequestType::BOX;
    			$requestStatus = RequestStatus::OPENED;
    			
    			if ($stmt->bind_param ( "iiiii", $reqType, $number, $requestStatus, $user->id, $user->client )) {
    				if ($stmt->execute ()) {
    					$requestID = $stmt->insert_id;
    					if ($requestID > 0) {
    						ModificationController::writeModification ( $this->db, "pedidos", $requestID, Modification::INSERT, $user->id );
    
    						$queryAvailableBox = "SELECT * FROM caixas 
    							LEFT JOIN pedidos ON box_request = req_id 
    							WHERE (box_request IS NULL OR 
    							req_status = " . RequestStatus::CANCELED . " OR 
    							req_status = " . RequestStatus::RETURNED . ") AND 
    							box_id IN(" . str_replace ( " , ", " ", implode ( ", ", $boxes ) ) . ") AND 
    							(box_blocked IS NULL OR box_blocked = FALSE) AND 
    							box_client = " . $user->client;
    
    						if ($result = $this->db->query ( $queryAvailableBox )) {
    							$queryUpdateDocRequest = "UPDATE caixas 
    								LEFT JOIN pedidos ON box_request = req_id
    								SET box_request = $requestID 
    								WHERE (
    									box_request IS NULL OR 
    									box_request = 0 OR 
    									req_status = " . RequestStatus::CANCELED . " OR 
    									req_status = " . RequestStatus::RETURNED . ") AND 
    									box_id IN(" . str_replace ( " , ", " ", implode ( ", ", $boxes ) ) . ") AND 
    									(box_blocked IS NULL OR box_blocked = FALSE) AND 
    									box_client = " . $user->client;
    
    							if ($result->num_rows > 0 && $this->db->query ( $queryUpdateDocRequest )) {
    								$numInserted = 0;
    								while ( $row = $result->fetch_object () ) {
    									$queryInsertRequestDocument = "INSERT INTO caixas_pedidas(dop_req, dop_box) VALUES(" . $requestID . ", " . $row->box_id . ")";
    									if (! $this->db->query ( $queryInsertRequestDocument )) {
    										break;
    									} else {
    										++ $numInserted;
    									}
    								}
    								$notificationController = new NotificationController($this->db);
    								$ok = $numInserted == $result->num_rows && $notificationController->registerNotification($user->id, $requestID, NotificationType::REQUEST, NotificationEvent::REGISTER);
    							}
    						}
    					}
    				}
    			}
    		}
    
    		if ($ok) {
    			$this->db->commit ();
    		} else {
    			$requestID = 0;
    			$this->db->rollback ();
    		}
		    
		}

		return $requestID;
	}

	/**
	 *
	 * @param int $id
	 * @return Request|NULL
	 */
	public function getRequest($id) {
		$request = NULL;
		$query = "SELECT * FROM pedidos " . "LEFT JOIN status_pedidos ON sta_id = req_status " . "LEFT JOIN modificacoes ON mod_table like 'pedidos' AND mod_tb_id = $id " . "LEFT JOIN users on usr_id = req_user " . "WHERE req_id = $id";

		if ($result = $this->db->query ( $query )) {
			if ($row = $result->fetch_object ()) {
				$request = Request::withRow ( $row );
			}
		}
		return $request;
	}

	/**
	 *
	 * @return KeyValue[]
	 */
	public function getRequestStatus() {
		$situations = array ();
		$query = "SELECT * FROM status_pedidos WHERE sta_dead = FALSE";
		if ($result = $this->db->query ( $query )) {
			while ( $row = $result->fetch_object () ) {
				$situations [] = new KeyValue ( $row->sta_id, $row->sta_name );
			}
		}
		return $situations;
	}

	/**
	 *
	 * @param Request $request
	 * @param int $status
	 * @param int $user_id
	 * @param string $receipt_file
	 * @return boolean
	 */
	public function setRequestStatus($request, $status, $user_id, $receipt_file = NULL) {
		$ok = FALSE;
		$notController = new NotificationController($this->db);
		$this->db->begin ();
		$query = "UPDATE pedidos SET req_status = $status ";

		if ($receipt_file != NULL) {
			switch ($status) {
				case RequestStatus::ATTENDEND :
					{
						$query .= ", req_receipt_file_1 = '$receipt_file' ";
					}
					break;
				case RequestStatus::COMPLETED :
					{
						$query .= ", req_receipt_file_2 = '$receipt_file' ";
					}
					break;
			}
		}

		$query .= "WHERE req_id = " . $request->getId ();

		if ($this->db->query ( $query )) {
			if (ModificationController::writeModification ( $this->db, "pedidos", $request->id, Modification::UPDATE . "2" . $status, $user_id )) {
				$update_error = FALSE;
				if ($status == RequestStatus::SENT || $status == RequestStatus::ATTENDEND) {
					if ($request->getType () == RequestType::DOCUMENT) {
						$query = "UPDATE documentos_pedidos SET dcr_status = $status WHERE dcr_request = " . $request->getId ();
					} else if ($request->getType () == RequestType::BOX) {
						$query = "UPDATE caixas_pedidas SET dop_status = $status WHERE dop_req = " . $request->getId ();
					}
					if (! $this->db->query ( $query )) {
						$update_error = TRUE;
					}
				}

				if (! $update_error && ($status == RequestStatus::COMPLETED || $status == RequestStatus::CANCELED)) {
					if ($request->getType () == RequestType::BOX) {// Se pedido do tipo caixa....
						$query = "UPDATE caixas set box_request = NULL WHERE box_request = " . $request->id;
						if ($this->db->query ( $query )) {
							$ok = TRUE;
						}
					} else if ($request->getType () == RequestType::DOCUMENT) {
						$ok = $this->freeDocumentsFromRequest($request);
					}
				} else {
					$ok = TRUE;
				}
				
				if($ok) {
					$notController->muteNotification(NotificationType::REQUEST, $request->getId());
				}
			}
		}

		if ($ok) {
			$this->db->commit ();
		} else {
			$this->db->rollback ();
		}

		return $ok;
	}

	/**
	 *
	 * @param Request $request
	 * @return array
	 */
	private function getBoxesFromRequest($request) {
		$boxes = array ();
		if ($request->getType () == RequestType::DOCUMENT) {
			$documents = $this->getRequestDocuments ( $request );
			foreach ( $documents as $doc ) {
				if (! in_array ( $doc->getDocument ()->getBox ()->getId (), $boxes )) {
					$boxes [] = $doc->getDocument ()->getBox ()->getId ();
				}
			}
		} else if ($request->getType () == RequestType::BOX) {
			// TODO Implementation required
		}
		return $boxes;
	}
	public function getRequestReceipts($req_id) {
		$receipts = array ();
		$query = "SELECT * FROM comprovantes WHERE com_request = $req_id";
		if ($result = $this->db->query ( $query )) {
			while ( $row = $result->fetch_object () ) {
				$receipts [] = new KeyValue ( $row->com_id, utf8_encode ( $row->com_file ) );
			}
		}
		return $receipts;
	}

	/**
	 *
	 * @param []int $docs
	 * @param User $user
	 * @return number
	 */
	function registerDocumentRequest($docs, $user) {
		$ok = FALSE;
		$requestID = 0;
		if (is_array ( $docs ) && count ( $docs ) > 0) {
			$this->db->begin ();
			$number = $this->getNextRequestNumber ( $user->client );

			// Registra o pedido de documento
			$query = "INSERT INTO pedidos(req_type, req_number, req_status, req_user, req_client) VALUES(?,?,?,?,?)";
			if ($number > 0 && $stmt = $this->db->prepare ( $query )) {
				$reqType = RequestType::DOCUMENT;
				$reqStatus = RequestStatus::OPENED;

				if ($stmt->bind_param ( "iiiii", $reqType, $number, $reqStatus, $user->id, $user->client )) {
					if ($stmt->execute ()) {
						$requestID = $stmt->insert_id;
						if ($requestID > 0) {
							ModificationController::writeModification ( $this->db, "pedidos", $requestID, Modification::INSERT, $user->id );
							$numInserted = 0;
							foreach ( $docs as $docID ) {
								// Verifica se a caixa não está em pedido de caixa, e o documento não está em pedido de documento
								$query = "SELECT * FROM caixas 
                                LEFT JOIN pedidos ON box_request = req_id " . 
								"WHERE (box_request IS NULL OR box_request = 0 OR req_status = " . RequestStatus::CANCELED . 
								" OR req_status = " . RequestStatus::RETURNED . ") " . 
								"AND box_id = (SELECT doc_box FROM documentos WHERE doc_id = $docID AND (doc_request IS NULL OR doc_request = 0) AND doc_dead=FALSE)" . 
								"AND box_client = " . $user->client;

								if ($result = $this->db->query ( $query )) {
									if ($row = $result->fetch_object ()) { // O documento está livre para ser adicionado ao pedido
										$queryInsertRequestDocument = "INSERT INTO documentos_pedidos(dcr_request, dcr_document, dcr_status) VALUES($requestID, $docID, $reqStatus)";
										if ($this->db->query ( $queryInsertRequestDocument )) {
											// Adiciona ID da request no documento
											$query = "UPDATE documentos set doc_request = $requestID WHERE doc_id = $docID";
											if ($this->db->query ( $query )) {
												++ $numInserted;
												// Altera estado da caixa impedindo pedido do tipo caixa nela
												$query = "UPDATE caixas SET box_blocked = TRUE WHERE box_id = (SELECT doc_box FROM documentos WHERE doc_id = $docID)";
												$this->db->query ( $query );
											}
										} else {
											break;
										}
									}
								}
							}
							$ok = $numInserted == count ( $docs );

							// Registra notificação aos usuários administradores
							$notificationController = new NotificationController($this->db);
							$ok = $ok && $notificationController->registerNotification($user->id, $requestID, NotificationType::REQUEST, NotificationEvent::REGISTER);
						}
					}
				}
			}
			if ($ok) {
				$this->db->commit ();
			} else {
				$requestID = 0;
				$this->db->rollback ();
			}
		}
		return $requestID;
	}

	/**
	 *
	 * @param Request $request
	 * @return RequestDocument[]
	 */
	public function getRequestDocuments($request) {
		$documents = array ();
		if ($request != NULL) {
			$query = "SELECT * FROM documentos  
                LEFT JOIN tipos_documentos ON doc_type = dct_id 
				LEFT JOIN caixas on box_id = doc_box 
				LEFT JOIN departamentos d ON d.dep_id = box_department 
				LEFT JOIN pedidos p ON box_request = p.req_id 
				LEFT JOIN users u ON u.usr_id = p.req_user
                INNER JOIN documentos_pedidos ON dcr_document = doc_id  
                WHERE doc_dead = FALSE AND dcr_request = " . $request->getId ();
			if ($result = $this->db->query ( $query )) {
				while ( $row = $result->fetch_object () ) {
					$documents [] = RequestDocument::withRow ( $row );
				}
			}
		}
		return $documents;
	}
	public function returnDocFromRequest($doc_id, $request_id, $user) {
		$ok = FALSE;
		$this->db->begin ();

		$query = "UPDATE documentos SET doc_request = NULL WHERE doc_request = $request_id AND doc_id = $doc_id";
		if ($this->db->query ( $query )) {
			$ok = TRUE && ModificationController::writeModification ( $this->db, "pedidos", $request_id, Modification::FREE_DOC_FROM_REQUEST, $user->id );
		}

		if ($ok) {
			$this->db->commit ();
		} else {
			$this->db->rollback ();
		}

		return $ok;
	}

	/**
	 * Verifica se um documento está liberado para ser pedido.
	 * Trava a linha da tabela de caixas e a linha da tabela de documentos.
	 *
	 * @uses LOCK IN SHARE MODE
	 *      
	 * @param int $doc_id
	 * @return boolean
	 */
	public function isDocFreeToOrder($doc_id) {
		$query = "SELECT * FROM caixas 
		LEFT JOIN pedidos ON box_request = req_id
		WHERE (
			box_request IS NULL OR 
			req_status = " . RequestStatus::CANCELED . " OR 
			req_status = " . RequestStatus::RETURNED . " OR 
			req_status = " . RequestStatus::RETURNING . " OR
			req_status = " . RequestStatus::COMPLETED . ") 
			AND box_id = 
			(
				SELECT doc_box 
				FROM documentos 
				LEFT JOIN documentos_pedidos ON dcr_request = doc_request 
				WHERE doc_id = $doc_id AND 
				(
					doc_request IS NULL OR
					(	dcr_status = NULL OR
						dcr_status = 2 OR
						dcr_status = 5 OR
						dcr_status = 6 
					)
				) AND doc_dead = FALSE
			)";
		// LOCK IN SHARE MODE;";
		echo $query;
		if ($result = $this->db->query ( $query )) {
			if ($row = $result->fetch_object ()) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Pega próximo número da sequência de pedidos de documentos
	 *
	 * @param int $client_id
	 * @return number
	 */
	public function getNextRequestNumber($client_id) {
		$number = 0;
		$query = "SELECT COALESCE(MAX(req_number), 0) + 1 AS number FROM pedidos WHERE req_client = $client_id";
		if ($result = $this->db->query ( $query )) {
			if ($row = $result->fetch_object ()) {
				$number = $row->number;
			}
		}
		return $number;
	}

	/**
	 * Retorna os documentos do pedidos que podem ser devolvidos
	 *
	 * @param int $req_id
	 * @return int[]
	 */
	public function getDevolutionAvailableDocumentIds($req_id) {
		$arrIds = array ();
		/*$query = "SELECT dcr_document 
		FROM documentos_pedidos 
		LEFT JOIN documentos_devolucoes ON dre_document = dcr_document 
		LEFT JOIN 
		WHERE dcr_request = $req_id AND dcr_status = " . RequestStatus::ATTENDEND . " AND dre_document IS NULL";*/
		$query = "SELECT *
		FROM documentos_pedidos
		WHERE dcr_request = $req_id AND dcr_status = " . RequestStatus::ATTENDEND;

		if ($result = $this->db->query ( $query )) {
			while ( $row = $result->fetch_object () ) {
				$arrIds [] = $row->dcr_id;
			}
		}
		return $arrIds;
	}
	
	public function getDevolutionAvailableBoxIds($req_id) {
		$arrIds = array ();
		$query = "SELECT *
		FROM caixas_pedidas
		WHERE dop_req = $req_id AND dop_status = " . RequestStatus::ATTENDEND;
		
		if ($result = $this->db->query ( $query )) {
			while ( $row = $result->fetch_object () ) {
				$arrIds [] = $row->dop_id;
			}
		}
		return $arrIds;
	}

	/**
	 * @param Request $request
	 * @return boolean
	 */	
	public function freeDocumentsFromRequest($request) {
		$ok = FALSE;

		if($request->getType() == RequestType::DOCUMENT) {
			/* / Se pedido do tipo documento libera os documentos desse pedido */
			$query = "UPDATE documentos SET doc_request = NULL WHERE doc_request = " . $request->getId ();
			if ($this->db->query ( $query )) {
				// Pega todas as caixas dos documentos
				$boxes = $this->getBoxesFromRequest ( $request );
				foreach ( $boxes as $box ) {
					// Existe mais pedidos(ativos) do tipo documento p/ a caixa?
					$query = "SELECT count(*) AS count FROM documentos ";
					$query .= "LEFT JOIN pedidos ON req_id = doc_request ";
					$query .= "WHERE doc_box = $box AND doc_request IS NOT NULL ";
					$query .= " AND req_status <> " . RequestStatus::CANCELED;
					$query .= " AND req_status <> " . RequestStatus::RETURNED;
					$query .= " AND req_status <> " . RequestStatus::COMPLETED;

					if ($result = $this->db->query ( $query )) {
						if ($row = $result->fetch_object ()) {
							if ($row->count == 0) {
								// Se não existe, atualiza box_blocked para FALSE
								$this->db->query ( "UPDATE caixas SET box_blocked = FALSE WHERE box_id = $box" );
							}
						}
					}
				}
				$ok = TRUE;
			}
		}

		return $ok;
	}
	
	/**
	 * @param Request $request
	 * @return boolean
	 */
	public function freeDocFromRequest($doc_id, $box_id, $request) {
		$ok = FALSE;

		if($request->getType() == RequestType::DOCUMENT) {
			/* / Se pedido do tipo documento libera os documentos desse pedido */
			$query = "UPDATE documentos SET doc_request = NULL WHERE doc_id = $doc_id AND doc_request = " . $request->getId ();
			if ($this->db->query ( $query )) {
				// Existe mais pedidos(ativos) do tipo documento p/ a caixa?
				$query = "SELECT count(*) AS count FROM documentos ";
				$query .= "LEFT JOIN pedidos ON req_id = doc_request ";
				$query .= "WHERE doc_box = $box_id AND doc_request IS NOT NULL ";
				$query .= " AND req_status <> " . RequestStatus::CANCELED;
				$query .= " AND req_status <> " . RequestStatus::RETURNED;
				$query .= " AND req_status <> " . RequestStatus::COMPLETED;
				
				if ($result = $this->db->query ( $query )) {
					if ($row = $result->fetch_object ()) {
						if ($row->count == 0) {
							// Se não existe, atualiza box_blocked para FALSE
							$this->db->query ( "UPDATE caixas SET box_blocked = FALSE WHERE box_id = $box_id" );
						}
					}
				}

				$ok = TRUE;
			}
		}
		
		return $ok;
	}
	
	public function freeBoxesFromRequest($req_id) {
		$query = "UPDATE caixas SET box_request = NULL WHERE box_request = $req_id";
		return $this->db->query ( $query );
	}
}