<?php
namespace Docbox\model;

class AbstractDocumentFormat {
    const BOOK = 1;
    const DOCUMENT = 2;
    /**
     * @var integer
     */
    var $value;
    function __construct($value) {
    	$this->value = $value;
    }
}