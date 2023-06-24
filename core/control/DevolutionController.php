<?php
namespace Docbox\control;

include_once (dirname ( __FILE__ ) . "/Controller.php");
include_once (dirname ( __FILE__ ) . "/../model/Box.php");
include_once (dirname ( __FILE__ ) . "/../model/Request.php");
include_once (dirname ( __FILE__ ) . "/../model/Devolution.php");
include_once (dirname ( __FILE__ ) . "/../model/RequestType.php");
include_once (dirname ( __FILE__ ) . "/../model/RequestStatus.php");
include_once (dirname ( __FILE__ ) . "/../model/DocumentFactory.php");
include_once (dirname ( __FILE__ ) . "/../model/DevolutionDocument.php");
include_once (dirname ( __FILE__ ) . "/../model/DevolutionBox.php");
include_once (dirname ( __FILE__ ) . "/../model/Modification.php");
include_once (dirname ( __FILE__ ) . "/ModificationController.php");
include_once (dirname ( __FILE__ ) . "/RequestController.php");
include_once (dirname ( __FILE__ ) . "/NotificationController.php");

use DocBox\model\NotificationEvent;
use DocBox\model\NotificationType;
use DocBox\model\Request;
use Docbox\model\Devolution;
use Docbox\model\DevolutionBox;
use Docbox\model\DevolutionDocument;
use Docbox\model\Modification;
use Docbox\model\RequestStatus;
use Docbox\model\RequestType;

class DevolutionController extends Controller {
	public function getDevolutionById($dev_id) {
		$devolution = NULL;
		$query = "SELECT * FROM devolucoes INNER JOIN users ON usr_id = ret_user WHERE ret_id = $dev_id";
		if ($result = $this->db->query ( $query )) {
			if ($row = $result->fetch_object ()) {
				$devolution = Devolution::withRow ( $row );
			}
		}
		return $devolution;
	}

	/**
	 * TODO dinossauro
	 * @param Request $request
	 * @param int $user_id
	 * @return boolean
	 */
	public function registerTotalRequestDevolution($request, $user_id) {
		$ok = FALSE;
		$returnID = 0;
		$this->db->begin ();

		$query = "UPDATE pedidos SET req_status = " . RequestStatus::RETURNING .
		" WHERE req_id = " . $request->getId () .
		" AND req_status = " . RequestStatus::ATTENDEND;

		if ($stmt = $this->db->prepare ( $query )) {
			if ($stmt->execute () && $stmt->affected_rows == 1) {

				if ($request->getType () == RequestType::DOCUMENT) {
					$query = "UPDATE documentos_pedidos
								SET dcr_status = " . RequestStatus::RETURNED . "
								WHERE dcr_request = " . $request->getId ();
								// . " AND dcr_status = " . RequestStatus::ATTENDEND;
				} else if ($request->getType () == RequestType::BOX) {
					$query = "UPDATE caixas_pedidas SET dop_status = " . RequestStatus::RETURNED . " WHERE dop_req = " . $request->getId () . " AND dop_status = " . RequestStatus::ATTENDEND;
				}
				if ($this->db->query ( $query )) {
					// Cria a devolução
					$nextReturnNumber = $this->getNextDevolutionNumber ( );
					$query = "INSERT INTO devolucoes(ret_number, ret_user, ret_req_type, ret_creation_time) VALUES($nextReturnNumber, $user_id, " . RequestType::DOCUMENT . ", CURRENT_TIMESTAMP)";
					if ($this->db->query ( $query )) {
						$returnID = $this->db->con->insert_id;
						ModificationController::writeModification ( $this->db, "pedidos", $request->getId (), Modification::UPDATE2RETURNING, $user_id, $returnID );

						$error = FALSE;
						if (ModificationController::writeModification ( $this->db, "devolucoes", $returnID, Modification::INSERT, $user_id )) {
							if ($result = $this->db->query ( "SELECT * FROM documentos_pedidos WHERE dcr_request = " . $request->getId () )) {
								while ( $row = $result->fetch_object () ) {
									$query = "INSERT INTO documentos_devolucoes(dre_return, dre_doc_requested) VALUES( $returnID, $row->dcr_id )";
									if (! $this->db->query ( $query )) {
										$error = TRUE;
										break;
									}
								}
							}
						}

						if (! $error) {
							$ok = TRUE;
							$notificationController = new NotificationController($this->db);
							$notificationController->registerNotification($user_id, $returnID, NotificationType::DEVOLUTION, NotificationEvent::REGISTER);
						}
					}
				}
			}
		}

		if ($ok) {
			$this->db->commit ();
			return $returnID;
		} else {
			$this->db->rollback ();
		}

		return 0;
	}

	/**
	 * Pega próximo número da sequência de devoluções de um pedido de documentos
	 *
	 * @param int $request_id
	 * @return number
	 */
	public function getNextDevolutionNumber() {
		$number = 0;
		$query = "SELECT COALESCE(MAX(ret_number), 0) + 1 AS number FROM devolucoes";
		if ($result = $this->db->query ( $query )) {
			if ($row = $result->fetch_object ()) {
				$number = $row->number;
			}
		}
		return $number;
	}

	/**
	 *
	 * @param Devolution $devolution
	 * @param string $token
	 * @param int $user_id
	 * @return boolean
	 */
	public function finishBoxDevolution($devolution, $token, $user_id) {
		$ok = FALSE;
		$notifController = new NotificationController($this->db);

		$this->db->begin ();
		$query = "UPDATE devolucoes
			INNER JOIN caixas_devolucoes ON bre_return = ret_id 
			INNER JOIN caixas_pedidas ON bre_box_requested = dop_id 
			INNER JOIN caixas ON box_id = dop_box 
			SET ret_file = '$token', 
			dop_status = " . RequestStatus::COMPLETED . ",
			box_request = NULL 
			WHERE ret_id = " . $devolution->getId ();

		if ($stmt = $this->db->prepare ( $query )) {
			if ($stmt->execute () && $stmt->affected_rows > 0) {
				if (ModificationController::writeModification ( $this->db, "devolucoes", $devolution->getId (), Modification::UPDATE2COMPLETED, $user_id )) {
					$notifController->muteNotification(NotificationType::DEVOLUTION, $devolution->getId ());
					$query = "SELECT * FROM pedidos 
					INNER JOIN caixas_pedidas ON dop_req = req_id 
					INNER JOIN caixas_devolucoes ON bre_box_requested = dop_id 
					WHERE bre_return = " . $devolution->getId ();

					if ($resReq = $this->db->query ( $query )) {
						$error = FALSE;
						while ( ! $error && $row = $resReq->fetch_object ()) {
							// (Número de caixas diferentes de COMPLETO == 0) ? Finaliza o pedido : Foo...
							$query = "SELECT count(*) AS count 
							FROM caixas_pedidas 
							WHERE dop_req = " . $row->req_id . 
							" AND dop_status <> " . RequestStatus::COMPLETED;

							if ($result = $this->db->query ( $query )) {
								if ($obj = $result->fetch_object ()) {
									if ($obj->count == 0) { // Todos completos
										$query = "UPDATE pedidos SET req_status = " . RequestStatus::COMPLETED . " WHERE req_id = " . $row->req_id . " AND req_status <> " . RequestStatus::COMPLETED;
										if ($stmt = $this->db->prepare ( $query )) {
											if($stmt->execute () && $stmt->affected_rows > 0) {// Executado com prepared para evitar cadastrar a modificação mais de uma vez
												if (ModificationController::writeModification ( $this->db, 'pedidos', $row->req_id, Modification::UPDATE2COMPLETED, $user_id )) {
													$reqController = new RequestController ( $this->db );
													$error = ! $reqController->freeBoxesFromRequest ( $row->req_id );
												}
											}
										}
									}
								}
							}
						}
						if(! $error) {
							$ok = TRUE;
						}
					}
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
	 * @param Devolution $devolution
	 * @return boolean
	 */
	public function deleteDevolutionFile($devolution, $user_id) {
		$ok = FALSE;
		$this->db->begin ();

		// Exclui do banco
		if ($this->db->query ( "UPDATE devolucoes SET ret_file=NULL WHERE ret_id = " . $devolution->getId () )) {
			ModificationController::writeModification ( $this->db, "devolucoes", $devolution->getId (), Modification::DELETE_DEVOLUTION_FILE, $user_id);
			// Exclui do disco
			$filename = dirname ( __FILE__ ) . "/../../devolution_files/" . $devolution->getFile ();
			if (file_exists ( $filename )) {
				if (unlink ( $filename )) {
					$ok = TRUE;
				}
			} else {
				$ok = TRUE;
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
	 * @param int $devolution_id
	 * @return \Docbox\model\DevolutionDocument[]
	 */
	public function getDocumentsFromDevolution($devolution_id) {
		$docs = array ();
		$devolution = $this->getDevolutionById ( $devolution_id );
		if ($devolution != NULL) {
			$query = "SELECT * FROM documentos_devolucoes
					INNER JOIN documentos_pedidos ON dre_doc_requested = dcr_id
					INNER JOIN documentos ON dcr_document = doc_id
					INNER JOIN pedidos ON req_id = dcr_request
					INNER JOIN caixas ON box_id = doc_box
					INNER JOIN departamentos ON dep_id = box_department
					INNER JOIN tipos_documentos ON dct_id = doc_type 
					LEFT JOIN users ON usr_id = req_user
					WHERE dre_return = $devolution_id";
			
			if ($result = $this->db->query ( $query )) {
				while ( $row = $result->fetch_object () ) {
					$docs [] = DevolutionDocument::withRow ( $row );
				}
			}
		}
		return $docs;
	}

	/**
	 * @param Devolution $devolution
	 * @return DevolutionBox[]
	 */
	public function getBoxesFromDevolution($devolution) {
		$boxes = array ();
		if ($devolution != NULL) {
			$query = "SELECT * from caixas_devolucoes
				INNER JOIN caixas_pedidas ON dop_id = bre_box_requested
				INNER JOIN caixas ON box_id = dop_box 
                LEFT JOIN departamentos d ON d.dep_id = box_department
                INNER JOIN pedidos ON dop_req = req_id
	            LEFT JOIN status_pedidos ON req_status = sta_id
                LEFT JOIN users ON usr_id = req_user
                WHERE bre_return = " . $devolution->getId ();
			if (! empty ( $query )) {
				if ($result = $this->db->query ( $query )) {
					while ( $row = $result->fetch_object () ) {
						$boxes [] = DevolutionBox::withRow ( $row );
					}
				}
			}
		}
		return $boxes;
	}

	/**
	 * Devolve documentos
	 *
	 * @param int[] $ids
	 *        	ID's dos documentos_pedidos
	 * @param integer $user_id
	 *        	Responsavel da ação
	 * @return number ID da devolução
	 */
	function registerPartialDocumentDevolution($ids, $user_id) {
		$ok = FALSE;
		$returnID = 0;
		$this->db->begin ();
		if (count ( $ids ) > 0) {
			$query = "UPDATE documentos_pedidos
						INNER JOIN pedidos ON req_id = dcr_request
						SET dcr_status = " . RequestStatus::RETURNED .
						" WHERE (req_status = " . RequestStatus::ATTENDEND .
						" OR req_status = " . RequestStatus::RETURNING . ")
						AND dcr_id IN(" . implode ( ',', $ids ) . ") AND dcr_status = " . RequestStatus::ATTENDEND;

			if ($stmt = $this->db->prepare ( $query )) {
				if ($stmt->execute () && $stmt->affected_rows == count (( $ids )) ) {
				    $query = "UPDATE pedidos
						INNER JOIN documentos_pedidos ON req_id = dcr_request
						SET req_status = " . RequestStatus::RETURNING .
						" WHERE (req_status = " . RequestStatus::ATTENDEND . " OR req_status = " . RequestStatus::RETURNING .
						") AND dcr_id IN(" . implode ( ',', $ids ) . ")";
				    if($this->db->query($query)) {
    					// Cria a devolução
    					$nextReturnNumber = $this->getNextDevolutionNumber ();
    					$query = "INSERT INTO devolucoes(ret_number, ret_user, ret_req_type, ret_creation_time) VALUES($nextReturnNumber, $user_id, " . RequestType::DOCUMENT . ", CURRENT_TIMESTAMP)";
    					if ($this->db->query ( $query )) {
    						$returnID = $this->db->con->insert_id;

    						$error = FALSE;
    						if (ModificationController::writeModification ( $this->db, "devolucoes", $returnID, Modification::INSERT, $user_id )) {
    							if ($result = $this->db->query ( "SELECT * FROM documentos_pedidos WHERE dcr_id IN(" . implode ( ',', $ids ) . ")" )) {
    								while ( $row = $result->fetch_object () ) {
    									$query = "INSERT INTO documentos_devolucoes(dre_return, dre_doc_requested) VALUES( $returnID, $row->dcr_id )";
    									if (! $this->db->query ( $query )) {
    										$error = TRUE;
    										break;
    									}
    									$error = ! ModificationController::writeModification ( $this->db, "pedidos", $row->dcr_request, Modification::UPDATE2RETURNING, $user_id, $returnID );
    								}
    							}
    						}

    						if (! $error) {
    							$ok = TRUE;
    							$notificationController = new NotificationController($this->db);
    							$notificationController->registerNotification($user_id, $returnID, NotificationType::DEVOLUTION, NotificationEvent::REGISTER);
    						}
    					}
				    }
				}
			}

			if ($ok) {
				$this->db->commit ();
			} else {
				$returnID = 0;
				$this->db->rollback ();
			}
		}
		return $returnID;
	}

	/**
	 *
	 * @param Devolution $devolution
	 * @param string $token
	 * @param integer $user_id
	 * @return boolean
	 */
	function finishDocumentDevolution($devolution, $token, $user_id) {
		$ok = FALSE;
		$reqController = new RequestController ( $this->db );
		$notifController = new NotificationController($this->db);

		$this->db->begin ();
		$query = "UPDATE devolucoes
			INNER JOIN documentos_devolucoes ON dre_return = ret_id
			INNER JOIN documentos_pedidos ON dre_doc_requested = dcr_id
			SET ret_file = '$token', dcr_status = " . RequestStatus::COMPLETED . "
			WHERE ret_id = " . $devolution->getId ();

		if ($stmt = $this->db->prepare ( $query )) {
			if ($stmt->execute () && $stmt->affected_rows > 0) {
				if (ModificationController::writeModification ( $this->db, "devolucoes", $devolution->getId (), Modification::UPDATE2COMPLETED, $user_id )) {
					$notifController->muteNotification(NotificationType::DEVOLUTION, $devolution->getId ());
					// Para cada PEDIDO verifica se deve FINALIZAR o pedido
					$query = "SELECT * FROM pedidos
					INNER JOIN documentos_pedidos ON dcr_request = req_id
					INNER JOIN documentos_devolucoes ON dre_doc_requested = dcr_id 
					INNER JOIN documentos ON doc_id = dcr_document 
					WHERE dre_return = " . $devolution->getId ();

					if ($resReq = $this->db->query ( $query )) {
						$error = FALSE;
						while ( ! $error && $row = $resReq->fetch_object ()) {
							// Número de documentos diferentes de COMPLETO == 0 ? Finaliza o pedido : Foo ...
							$query = "SELECT count(*) AS count
							FROM documentos_pedidos
							WHERE dcr_request = $row->req_id
							AND dcr_status <> " . RequestStatus::COMPLETED;

							$request = $reqController->getRequest ( $row->req_id );

							if ($result = $this->db->query ( $query )) {
								if ($obj = $result->fetch_object ()) {
									if ($obj->count == 0) { // Todos completos
										$query = "UPDATE pedidos SET req_status = " . RequestStatus::COMPLETED . " WHERE req_id = " . $row->req_id . " AND req_status <> " . RequestStatus::COMPLETED;
										if ($stmt = $this->db->prepare ( $query )) {
											if($stmt->execute () && $stmt->affected_rows > 0) {// Executado com prepared para evitar cadastrar a modificação mais de uma vez
    											if (ModificationController::writeModification ( $this->db, 'pedidos', $row->req_id, Modification::UPDATE2COMPLETED, $user_id )) {
    												$error = ! $reqController->freeDocumentsFromRequest ( $request );
    											}
										    }
										}
									} else {
										$error = ! $reqController->freeDocFromRequest ($row->dcr_document, $row->doc_box, $request );
									}
								} else {
									$error = TRUE;
								}
							} else {
								$error = TRUE;
							}
						}
						$ok = ! $error;
					}
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
	 * Devolve documentos
	 *
	 * @param int[] $ids
	 *        	ID's dos documentos_pedidos
	 * @param integer $user_id
	 *        	Responsavel da ação
	 * @return number ID da devolução
	 */
	function registerPartialBoxDevolution($ids, $user_id) {
	    $ok = FALSE;
	    $returnID = 0;
	    $this->db->begin ();
	    if (count ( $ids ) > 0) {
	        $query = "UPDATE caixas_pedidas
						INNER JOIN pedidos ON req_id = dop_req
						SET dop_status = " . RequestStatus::RETURNED .
						" WHERE (req_status = " . RequestStatus::ATTENDEND .
						" OR req_status = " . RequestStatus::RETURNING . ")
						AND dop_id IN(" . implode ( ',', $ids ) . ") AND dop_status = " . RequestStatus::ATTENDEND;

	        if ($stmt = $this->db->prepare ( $query )) {
	            if ($stmt->execute () && $stmt->affected_rows == count (( $ids )) ) {
	                $query = "UPDATE pedidos
									INNER JOIN caixas_pedidas ON req_id = dop_req
									SET req_status = " . RequestStatus::RETURNING .
									" WHERE (req_status = " . RequestStatus::ATTENDEND . " OR req_status = " . RequestStatus::RETURNING .
									") AND dop_id IN(" . implode ( ',', $ids ) . ")";
	                if($this->db->query($query)) {
	                    // Cria a devolução
	                    $nextReturnNumber = $this->getNextDevolutionNumber ();
	                    $query = "INSERT INTO devolucoes(ret_number, ret_user, ret_req_type, ret_creation_time) VALUES($nextReturnNumber, $user_id, " . RequestType::BOX . ", CURRENT_TIMESTAMP)";
	                    if ($this->db->query ( $query )) {
	                        $returnID = $this->db->con->insert_id;

	                        $error = FALSE;
	                        if (ModificationController::writeModification ( $this->db, "devolucoes", $returnID, Modification::INSERT, $user_id )) {
	                            if ($result = $this->db->query ( "SELECT * FROM caixas_pedidas WHERE dop_id IN(" . implode ( ',', $ids ) . ")" )) {
	                                while ( $row = $result->fetch_object () ) {
	                                    $query = "INSERT INTO caixas_devolucoes(bre_return, bre_box_requested) VALUES( $returnID, $row->dop_id )";
	                                    if (! $this->db->query ( $query )) {
	                                        $error = TRUE;
	                                        break;
	                                    }
	                                    $error = ! ModificationController::writeModification ( $this->db, "pedidos", $row->dop_req, Modification::UPDATE2RETURNING, $user_id, $returnID );
	                                }
	                            }
	                        }

	                        if (! $error) {
	                            $ok = TRUE;
	                            $notificationController = new NotificationController($this->db);
	                            $notificationController->registerNotification($user_id, $returnID, NotificationType::DEVOLUTION, NotificationEvent::REGISTER);
	                        }
	                    }
	                }
	            }
	        }

	        if ($ok) {
	            $this->db->commit ();
	        } else {
	            $returnID = 0;
	            $this->db->rollback ();
	        }
	    }
	    return $returnID;
	}
}
