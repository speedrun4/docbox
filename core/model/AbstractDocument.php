<?php
namespace Docbox\model;

include_once( dirname(__FILE__) . "/Box.php");
include_once( dirname(__FILE__) . "/DocumentType.php");
include_once( dirname(__FILE__) . "/AbstractDocumentFormat.php");

class AbstractDocument {
    /**
     * @var integer
     */
    var $id;
    /**
     * @var integer
     */
    var $client;
    /**
     * @var Box
     */
    var $box;
    /**
     * @var integer
     */
    var $year;
    /**
     * @var DocumentType
     */
    var $type;
    /**
     * 
     * @var AbstractDocumentFormat
     */
    var $format;
    /**
     * @var integer
     */
    var $request = 0;
    /**
     * @var integer
     */
    var $volume;
    /**
     * @var boolean
     */
    var $dead = false;
    
    public function loadRow($row) {
        $this->id = $row->doc_id;
        $this->client = $row->doc_client;
        $this->box = Box::withRow($row);
        $this->year = $row->doc_year;
        $this->type = DocumentType::withRow($row);
        $this->volume = $row->doc_volume;
        $this->request = $row->doc_request;
        $this->dead = $row->doc_dead;
    }
    
    /**
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return integer
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * @param integer $client
     */
    public function setClient($client) {
        $this->client = $client;
    }

    /**
     * @return Box
     */
    public function getBox() {
        return $this->box;
    }

    /**
     * @param Box $box
     */
    public function setBox($box) {
        $this->box = $box;
    }

    /**
     * @return integer
     */
    public function getYear() {
        return $this->year;
    }

    /**
     * @param integer $year
     */
    public function setYear($year) {
        $this->year = $year;
    }

    /**
     * @return DocumentType
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param DocumentType $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return integer
     */
    public function getVolume() {
        return $this->volume;
    }

    /**
     * @param integer $volume
     */
    public function setVolume($volume) {
        $this->volume = $volume;
    }

    /**
     * @return AbstractDocumentFormat
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * @param AbstractDocumentFormat $format
     */
    public function setFormat($format) {
        $this->format = $format;
    }

    /**
     * @return number
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * @param number $request
     */
    public function setRequest($request) {
        $this->request = $request;
    }

	/**
     * @return boolean
     */
    public function isDead() {
        return $this->dead;
    }

    /**
     * @param boolean $dead
     */
    public function setDead($dead) {
        $this->dead = $dead;
    }
}