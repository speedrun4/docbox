<?php
namespace Docbox\model;

include_once (dirname ( __FILE__ ) . "/Book.php");
include_once (dirname ( __FILE__ ) . "/DocumentFactory.php");
class RequestDocument {
	/**
	 *
	 * @var integer
	 */
	var $id;

	/**
	 *
	 * @var integer
	 */
	var $status;

	/**
	 *
	 * @var Document
	 */
	var $document;
	static function withRow($row) {
		$doc = new self ();
		$doc->id = $row->dcr_id;
		$doc->status = $row->dcr_status;
		$doc->document = DocumentFactory::create ( $row );
		return $doc;
	}

	/**
	 *
	 * @return number
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * @param number $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 *
	 * @return number
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 *
	 * @return \Docbox\model\Document
	 */
	public function getDocument() {
		return $this->document;
	}

	/**
	 *
	 * @param number $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 *
	 * @param \Docbox\model\Document $document
	 */
	public function setDocument($document) {
		$this->document = $document;
	}
}