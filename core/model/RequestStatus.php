<?php
namespace Docbox\model;

class RequestStatus {
	const OPENED = 1; // ABERTO -> Em pedido
	const SENT = 3; // EM ENVIO -> Em pedido
	const ATTENDEND = 4; // ATENDIDO -> Em pedido
	const CANCELED = 2; // CANCELADO -> Em estoque
	const RETURNED = 5; // DEVOLVIDO -> Em estoque
	const RETURNING = 7; // Devolvendo -> Parte em estoque, somente para o pedido(Request) não para o documento(Document)
	const COMPLETED = 6; // FINALIZADO -> Em estoque

	public static function isInStock($status) {
		return $status == RequestStatus::CANCELED ||
		$status == RequestStatus::RETURNED ||
		$status == RequestStatus::COMPLETED;
	}
}