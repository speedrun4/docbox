<?php
namespace Docbox\model;

include_once (dirname(__FILE__) . "/Book.php");
include_once (dirname(__FILE__) . "/Document.php");

class DocumentFactory {
	public static function create($row) {
		if($row->doc_book == 1) {
			return Book::withRow($row);
		} else {
			return Document::withRow($row);
		}
		return NULL;
	}
}
?>