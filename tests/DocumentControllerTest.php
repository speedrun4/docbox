<?php

namespace Docbox\tests;

require './vendor/autoload.php';

include_once (dirname ( __FILE__ ) . "/../core/model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../core/control/DocumentController.php");

use PHPUnit\Framework\TestCase;
use Docbox\model\DbConnection;
use Docbox\control\DocumentController;

class DocumentControllerTest extends TestCase {
    protected static $db;
    protected static $controller;

	public static function setUpBeforeClass(): void {
		self::$db = new DbConnection ();
		self::$controller = new DocumentController ( self::$db );
	}

    /**
	 * Prepares the environment before running a test.
	 */
	protected function setUp(): void {
		parent::setUp ();
	}

    /**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown(): void {
		parent::tearDown ();
	}

    public function testGetDocumentByBoxFilename()
    {
        $box_id = 2;
        $filename = "PTC0131_1996.pdf";
        $client_id = 1;
        $document = self::$controller->getDocumentByBoxFilename($box_id, $filename, $client_id);
		var_dump($document);
        $this->assertTrue($document != NULL);
    }
}