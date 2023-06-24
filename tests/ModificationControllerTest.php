<?php
include_once(dirname(__FILE__) . "/../core/model/DbConnection.php");
include_once(dirname(__FILE__) . "/../core/control/RequestController.php");

use Docbox\control\ModificationController;
use Docbox\model\DbConnection;
use PHPUnit\Framework\TestCase;

/**
 *  test case.
 */
class ModificationControllerTest extends TestCase {
	/**
	 * @var DbConnection
	 */
	static protected $db;

	public static function setUpBeforeClass():void {
		self::$db = new DbConnection();
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

	public function testWriteModification() {
		$this->assertTrue(ModificationController::writeModification(self::$db, 'pedidos', 1, 'I', 1));
	}
}