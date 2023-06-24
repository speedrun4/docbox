<?php
namespace Docbox\model;

class DevolutionDocument {
	var $returnID;
	var $docID;
	/**
	 * @var RequestDocument
	 */
	var $requestDocument;
	var $requestID;
	var $requestNumber;
	
	static function withRow($row) {
		$obj = new self();
		$obj->returnID = $row->dre_return;
		$obj->docID = $row->dre_doc_requested;
		$obj->requestID = $row->req_id;
		$obj->requestNumber = $row->req_number;
		$obj->requestDocument = RequestDocument::withRow($row);
		return $obj;
	}
	/**
	 * @return mixed
	 */
	public function getReturnID() {
		return $this->returnID;
	}

	/**
	 * @param mixed $returnID
	 */
	public function setReturnID($returnID) {
		$this->returnID = $returnID;
	}

	/**
	 * @return mixed
	 */
	public function getDocID() {
		return $this->docID;
	}

	/**
	 * @param mixed $docID
	 */
	public function setDocID($docID) {
		$this->docID = $docID;
	}

	/**
	 * @return mixed
	 */
	public function getRequestDocument() {
		return $this->requestDocument;
	}

	/**
	 * @param mixed $document
	 */
	public function setDocument($requestDocument) {
		$this->requestDocument = $requestDocument;
	}
	/**
	 * @return mixed
	 */
	public function getRequestID() {
		return $this->requestID;
	}

	/**
	 * @param mixed $requestID
	 */
	public function setRequestID($requestID) {
		$this->requestID = $requestID;
	}

	/**
	 * @return mixed
	 */
	public function getRequestNumber() {
		return $this->requestNumber;
	}

	/**
	 * @param mixed $requestNumber
	 */
	public function setRequestNumber($requestNumber) {
		$this->requestNumber = $requestNumber;
	}

}