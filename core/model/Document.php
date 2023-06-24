<?php
namespace Docbox\model;

use DateTime;

include_once( dirname(__FILE__) . "/DocumentType.php");
include_once (dirname(__FILE__) . "/AbstractDocument.php");
include_once( dirname(__FILE__) . "/Box.php");

class Document extends AbstractDocument {
	/**
	 * @var integer
	 */
	var $number;
	/**
	 * @var string
	 */
	var $letter;
	/**
	 * @var string
	 */
	var $company;
	/**
	 * @var DateTime
	 */
	var $date;
	/**
	 * @var string
	 */
	var $file;
	/**
	 * @var string
	 */
	var $hash;
	/**
	 * @var int
	 */
	var $pageCount = 0;

	public function __construct() {
	    $this->format = AbstractDocumentFormat::DOCUMENT;
	}

	public static function withRow($row) {
		$instance = new self();
        $instance->loadRow($row);
        $instance->letter = strtoupper($row->doc_letter);
        $instance->company = $row->doc_company;
        $instance->date = $row->doc_date;
        $instance->number = $row->doc_number;
        if(!empty($instance->date)) {
            $instance->date = \DateTime::createFromFormat("Y-m-d", $instance->date);
        }
        $instance->file = utf8_encode($row->doc_file);
        $instance->hash = $row->doc_hash;
        if(isset($row->doc_page_count)) $instance->setPageCount($row->doc_page_count);

		return $instance;
	}
	
    /**
     * @return integer
     */
    public function getNumber() {
        return $this->number;
    }
    /**
     * @param integer $number
     */
    public function setNumber($number) {
        $this->number = $number;
    }
    /**
     * @return string
     */
    public function getLetter() {
        return $this->letter;
    }
    /**
     * @param string $letter
     */
    public function setLetter($letter) {
        $this->letter = $letter;
    }
    /**
     * @return string
     */
    public function getCompany() {
        return $this->company;
    }
    /**
     * @param string $company
     */
    public function setCompany($company) {
        $this->company = $company;
    }
    /**
     * @return DateTime
     */
    public function getDate() {
        return $this->date;
    }
    /**
     * @param DateTime $date
     */
    public function setDate($date) {
        $this->date = $date;
    }
    /**
     * @return string
     */
    public function getFile() {
        return $this->file;
    }
    /**
     * @param string $file
     */
    public function setFile($file) {
        $this->file = $file;
    }
	/**
	 * @return string
	 */
	public function getHash() {
		return $this->hash;
	}
	/**
	 * @param string $hash
	 */
	public function setHash($hash) {
		$this->hash = $hash;
	}
	/**
	 * @return number
	 */
	public function getPageCount() {
		return $this->pageCount;
	}
	/**
	 * @param number $pageCount
	 */
	public function setPageCount($pageCount) {
		$this->pageCount = $pageCount;
	}
}