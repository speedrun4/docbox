<?php
include_once (dirname ( __FILE__ ) . "/Controller.php");
include_once (dirname ( __FILE__ ) . "/../model/Book.php");
include_once (dirname ( __FILE__ ) . "/ModificationController.php");
include_once (dirname(__FILE__) . "/../model/Book.php");

use Docbox\control\Controller;
use Docbox\control\ModificationController;
use Docbox\model\Book;

class BookController extends Controller {
    function insertBook($book, $user_id) {
        $id = 0;
        $isBook = TRUE;
        $ok = FALSE;
        $this->db->begin();
        $query = "INSERT INTO documentos(doc_book, doc_client, doc_box, doc_year, doc_type, doc_volume, doc_num_from, doc_num_to) VALUES(?,?,?,?,?,?,?,?)";

        if($stmt = $this->db->prepare($query)) {
            if($stmt->bind_param("iiiiiiii", $isBook, $book->client, $book->box->id, $book->year, $book->type->id, $book->volume, $book->numFrom, $book->numTo)) {
                if($stmt->execute()) {
                    $id = $stmt->insert_id;
                    if($id != 0) {
                        if(ModificationController::writeModification($this->db, "livros", $id, "I", $user_id)) {
                            $ok = TRUE;
                        }
                    }
                }
            }
        }

        if($ok) {
            $this->db->commit();
        } else {
            $this->db->rollback();
            $id = 0;
        }

        return $id;
    }

    /**
     * @param Book $book
     */
    public function bookExists($book) {
        $query = "SELECT * FROM documentos " .
        "WHERE doc_book = TRUE AND doc_client = " . $book->getClient() .
        " and doc_box = ". $book->getBox()->getId() . 
        " and doc_type = ". $book->getType()->getId() . 
        " and doc_year = ". $book->getYear() . 
        " and doc_num_from = ". $book->getNumFrom() . 
        " and doc_num_to = " . $book->getNumTo() .
        ($book->volume > 0 ? " AND doc_volume = " . $book->getVolume() : " AND doc_volume IS NULL");

        if($result = $this->db->query($query)) {
            if($result->fetch_object()) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Verifica se existe outro livro como os mesmos valores
     * @param Book $book
     * @return boolean
     */
    public function existsAnother($book) {
        $query = "SELECT * FROM documentos " .
            "WHERE doc_book = TRUE AND doc_client = " . $book->getClient() . " AND " .
            "doc_id <> " . $book->getId() . " AND " .
            "doc_box = " . $book->getBox()->getId() . " AND " .
            "doc_type = " . $book->getType()->getId() . " AND " .
            "doc_year = " . $book->year .
            " and doc_num_from = ". $book->getNumFrom() .
            " and doc_num_to = " . $book->getNumTo() .
            " and doc_dead = FALSE " .
            ($book->volume > 0 ? " AND doc_volume = " . $book->getVolume() : " AND doc_volume IS NULL");

        if($result = $this->db->query($query)) {
            if($result->fetch_object()) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * @param Book $book
     * @return boolean
     */
    public function updateBook($book, $user_id) {
        $query = "UPDATE documentos SET doc_box=?, doc_type=?, doc_year=?, doc_num_from = ?, doc_num_to = ?, doc_volume=? WHERE doc_id = ?";
        if($stmt = $this->db->prepare($query)) {
            if($stmt->bind_param("iiiiiii", $book->box->id, $book->type->id, $book->year, $book->numFrom, $book->numTo, $book->volume, $book->id)) {
                if($stmt->execute() && ModificationController::writeModification($this->db, "livros", $book->id, "U", $user_id)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * @param integer $id
     * @return Book
     */
    public function getBookById($id) {
        $query = "SELECT * FROM documentos
				LEFT JOIN tipos_documentos ON doc_type = dct_id
				LEFT JOIN caixas on box_id = doc_box
				LEFT JOIN departamentos d ON d.dep_id = box_department
				LEFT JOIN pedidos p ON box_request = p.req_id
				LEFT JOIN users u ON u.usr_id = p.req_user 
				WHERE doc_id = $id AND doc_book = TRUE AND doc_dead = FALSE";
        if($result = $this->db->query($query)) {
            if($row = $result->fetch_object()) {
                $book = Book::withRow($row);
            }
        }
        return $book;
    }
    
    /**
     * Guess what...
     * @param integer $book_id
     * @param integer $user_id
     */
    public function deleteBook($book_id, $user_id) {
        $ok = TRUE;
        $this->db->begin();
        $query = "update documentos SET doc_dead = TRUE WHERE doc_id = ?";
        if($stmt = $this->db->prepare($query)) {
            if($stmt->bind_param("i", $book_id)) {
                if($stmt->execute() && ModificationController::writeModification($this->db, "livros", $book_id, "D", $user_id)) {
                    $ok = TRUE;
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
}