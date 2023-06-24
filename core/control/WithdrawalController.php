<?php
namespace Docbox\control;

include_once (dirname ( __FILE__ ) . "/Controller.php");
include_once (dirname ( __FILE__ ) . "/../model/User.php");
include_once (dirname ( __FILE__ ) . "/../model/Withdrawal.php");
include_once (dirname ( __FILE__ ) . "/../model/Modification.php");
include_once (dirname ( __FILE__ ) . "/../model/Notification.php");
include_once (dirname ( __FILE__ ) . "/ModificationController.php");
include_once (dirname ( __FILE__ ) . "/../model/WithdrawalDoc.php");
include_once (dirname ( __FILE__ ) . "/NotificationController.php");
include_once (dirname ( __FILE__ ) . "/../model/WithdrawalStatus.php");

use DocBox\model\NotificationEvent;
use Docbox\model\Modification;
use Docbox\model\User;
use Docbox\model\Withdrawal;
use Docbox\model\WithdrawalDoc;
use Docbox\model\WithdrawalStatus;
use Docbox\model\NotificationType;

class WithdrawalController extends Controller {
	/**
	 * @param WithdrawalDoc[] $withdrawalDocs
	 * @param User $user
	 */
	public function insertWithdrawal($withdrawalDocs, $user) {
		$id = 0;
		if (count ( $withdrawalDocs ) > 0) {
			$ok = false;
			$query = "INSERT INTO retiradas(pul_id, pul_client, pul_number, pul_status, pul_user_requested) VALUES(NULL,?,?,?,?)";
			$this->db->begin ();
			$number = $this->getNextWithdrawalNumber( $user->client );
			$status = WithdrawalStatus::OPEN;

			if ($stmt = $this->db->prepare ( $query )) {
				if ($stmt->bind_param ( "iiii", $user->client, $number, $status, $user->id )) {
					if ($stmt->execute ()) {
						if (($id = $stmt->insert_id) > 0) {
							$ok = TRUE;
							foreach ( $withdrawalDocs as $doc ) {
								$doc->setIdWithdrawal ( $id );
								$query = "INSERT INTO retirada_documentos(pud_id_withdrawal, pud_number, pud_year) VALUES($doc->keyWithdrawal, $doc->number, $doc->year)";
								if (! $this->db->query ( $query )) {
									$ok = FALSE;
								}
							}

							if($ok) {
								$ok = ModificationController::writeModification($this->db, "retiradas", $id, Modification::INSERT, $user->id, "Inserted (" . count($withdrawalDocs) . ") docs");
							}

							if($ok) {
								$notificationController = new NotificationController($this->db);
								$ok = $notificationController->registerNotification($user->id, $id, NotificationType::WITHDRAWAL, NotificationEvent::REGISTER);
							}
						}
					}
				}
			}

			if ($ok) {
				$this->db->commit ();
			} else {
				$id = 0;
				$this->db->rollback ();
			}
		}

		return $id;
	}

	public function getWithdrawalById($id, $client) {
		$withdrawal = NULL;
		$query = "SELECT * FROM retiradas LEFT JOIN users on usr_id = pul_user_requested WHERE pul_id = $id AND pul_client = $client";

		if ($result = $this->db->query ( $query )) {
			if ($row = $result->fetch_object ()) {
				$withdrawal = Withdrawal::withRow ( $row );
			}
		}
		return $withdrawal;
	}

	/**
	 *
	 * @param Withdrawal $withdrawal
	 * @return array
	 */
	public function getWithdrawalDocs($withdrawal) {
		$docs = array ();
		$query = "SELECT * FROM retirada_documentos WHERE pud_id_withdrawal = $withdrawal->id and pud_dead = FALSE";
		if ($result = $this->db->query ( $query )) {
			while ( $row = $result->fetch_object () ) {
				$docs [] = new WithdrawalDoc ( $row->pud_number, $row->pud_year, $withdrawal->id );
			}
		}
		return $docs;
	}

	/**
	 * @param integer $withdrawalID
	 * @return boolean
	 */
	public function finishWithdrawal($withdrawalID, $token, $user_id) {
		$ok = FALSE;
		$this->db->begin ();
		$query = "UPDATE retiradas SET pul_status = ?, pul_receipt = ? WHERE pul_id = ? AND pul_status = " . WithdrawalStatus::OPEN;

		if ($stmt = $this->db->prepare ( $query )) {
			$status = WithdrawalStatus::FINISHED;
			if($stmt->bind_param("isi", $status, $token, $withdrawalID)) {
				$stmt->execute();
				if($ok = $stmt->affected_rows > 0) {
					$ok = ModificationController::writeModification($this->db, "retiradas", $withdrawalID, Modification::UPDATE2COMPLETED, $user_id, $token);
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
	 * Para atualizar os documentos da retirada
	 * 
	 * @param integer $withdrawalID
	 * @param array $withdrawalDocs
	 * @return boolean
	 */
	public function updateWithdrawal($withdrawalID, $withdrawalDocs=NULL) {
		$ok = TRUE;
		$this->db->begin();
		// Exclui todos os documentos anteriores
		if ($this->deleteWithdrawalDocs ( $withdrawalID )) {
			// Adiciona os novos
			foreach ( $withdrawalDocs as $doc ) {
				$doc->setIdWithdrawal ( $withdrawalID );
				$query = "INSERT INTO retirada_documentos(pud_id_withdrawal, pud_number, pud_year)" .
				" VALUES($doc->keyWithdrawal, $doc->number, $doc->year)";
				if (! $this->db->query ( $query )) {
					$ok = FALSE;
				}
			}
		} else {
			$ok = FALSE;
		}

		if ($ok) {
			$this->db->commit ();
		} else {
			$this->db->rollback ();
		}

		return $ok;
	}

	public function addDocsToWithdrawal($withdrawalID, $docs) {
		$ok = TRUE;
		foreach ( $docs as $doc ) {
			$doc->setIdWithdrawal ( $withdrawalID );
			$query = "INSERT INTO retirada_documentos(pud_id_withdrawal, pud_number, pud_year) VALUES($doc->keyWithdrawal, $doc->number, $doc->year)";
			if (! $this->db->query ( $query )) {
				$ok = FALSE;
			}
		}

		if ($ok) {
			$this->db->commit ();
		} else {
			$this->db->rollback ();
		}

		return $ok;
	}

	public function deleteWithdrawalDocs($idWithdrawal) {
		$query = "UPDATE retirada_documentos SET pud_dead = TRUE WHERE pud_id_withdrawal = $idWithdrawal";
		return $this->db->query($query);
	}

	public function deleteWithdrawal($id, $user_id) {
		$ok = FALSE;
		$this->db->begin();

		// Exclui do banco
		if ($this->db->query ( "UPDATE retiradas SET pul_dead = TRUE WHERE pul_id = $id" )) {
			ModificationController::writeModification ( $this->db, "retiradas", $id, Modification::DELETE, $user_id );
			$ok = TRUE;
		}

		if ($ok) {
			$this->db->commit();
		} else {
			$ok = FALSE;
			$this->db->rollback();
		}

		return $ok;
	}

	/**
	 * Pega próximo número da sequência de pedidos de documentos
	 *
	 * @param int $client_id
	 * @return number
	 */
	public function getNextWithdrawalNumber($client_id) {
		$number = 0;
		$query = "SELECT COALESCE(MAX(pul_number), 0) + 1 AS number FROM retiradas WHERE pul_client = $client_id AND pul_dead = FALSE";
		if ($result = $this->db->query ( $query )) {
			if ($row = $result->fetch_object ()) {
				$number = $row->number;
			}
		}
		return $number;
	}

	public function cancelWithdrawal($withdrawalID, $user_id) {
		$ok = FALSE;
		$query = "UPDATE retiradas SET pul_status = ? WHERE pul_id = $withdrawalID AND pul_status = " . WithdrawalStatus::OPEN;
		if($stmt = $this->db->prepare($query)) {
			$status = WithdrawalStatus::CANCELLED;
			if($stmt->bind_param("i", $status)) {
				$stmt->execute();
				if($stmt->affected_rows > 0) {
					$ok = ModificationController::writeModification($this->db, "retiradas", $withdrawalID, Modification::CANCEL, $user_id);
				}
				if($ok) {
					$notificationController = new NotificationController($this->db);
					$notificationController->muteNotification(NotificationType::WITHDRAWAL, $withdrawalID);
				}
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
	 * @return boolean
	 */
	public function updateReceipt($withdrawalID, $receipt, $user_id) {
		$ok = FALSE;
		$query = "UPDATE retiradas SET pul_receipt = ? WHERE pul_id = $withdrawalID AND pul_status = " . WithdrawalStatus::FINISHED;
		if($stmt = $this->db->prepare($query)) {
			if($stmt->bind_param("s", $receipt)) {
				$stmt->execute();
				if($stmt->affected_rows > 0) {
					// TODO Colocar na informação extra o nome do arquivo que foi substituido
					$ok = ModificationController::writeModification($this->db, "retiradas", $withdrawalID, Modification::UPDATE_RECEIPT, $user_id, $receipt);
				}
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
	 * @param int $withdrawalID
	 * @param int[] $docs
	 * @param int $user_id
	 * 
	 * @return boolean
	 */
	public function removeDocsFromWithdrawal($withdrawalID, $docs, $user_id) {
		$ok = TRUE;
		$this->db->begin();
		$query = "UPDATE retirada_documentos SET pud_dead = TRUE WHERE pud_id_withdrawal = $withdrawalID AND pud_id = ?";
		foreach($docs as $doc) {
			if($stmt = $this->db->prepare($query)) {
				if($stmt->bind_param("i", $doc)) {
					$stmt->execute();
					if($stmt->affected_rows < 1) {
						$ok = FALSE;
					}
				}
			}
		}

		if($ok) {
			$ok = ModificationController::writeModification($this->db, "retiradas", $withdrawalID, Modification::UPDATE, $user_id, "Removed (" . count($docs) . ") documents");
		}

		if($ok) {
			$this->db->commit();
		} else {
			$this->db->rollback();
		}

		return $ok;
	}
}