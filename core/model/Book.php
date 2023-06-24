<?php
namespace Docbox\model;

include_once (dirname(__FILE__) . "/Box.php");
include_once (dirname(__FILE__) . "/AbstractDocument.php");
include_once (dirname(__FILE__) . "/DocumentType.php");

class Book extends AbstractDocument {
    /**
     * @var integer
     */
    var $numFrom;

    /**
     * @var integer
     */
    var $numTo;
    
    public function __construct() {
        $this->format = AbstractDocumentFormat::BOOK;
    }

    public static function withRow($row) {
        $instance = new self();
        $instance->loadRow($row);
        $instance->numFrom = $row->doc_num_from;
        $instance->numTo = $row->doc_num_to;

        return $instance;
    }

    /**
     *
     * @return number
     */
    public function getNumFrom() {
        return $this->numFrom;
    }

    /**
     *
     * @return number
     */
    public function getNumTo() {
        return $this->numTo;
    }

    /**
     *
     * @param number $numFrom
     */
    public function setNumFrom($numFrom) {
        $this->numFrom = $numFrom;
    }

    /**
     *
     * @param number $numTo
     */
    public function setNumTo($numTo) {
        $this->numTo = $numTo;
    }
}