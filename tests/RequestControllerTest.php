<?php
include_once (dirname ( __FILE__ ) . "/../core/model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../core/control/RequestController.php");

use Docbox\control\RequestController;
use Docbox\model\DbConnection;
use Docbox\model\User;
use PHPUnit\Framework\TestCase;

/**
 * test case.
 */
class RequestControllerTest extends TestCase {
	/**
	 *
	 * @var RequestController
	 */
	protected static $controller;
	/**
	 *
	 * @var DbConnection
	 */
	protected static $db;
	public static function setUpBeforeClass(): void {
		self::$db = new DbConnection ();
		self::$controller = new RequestController ( self::$db );
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
	public function testGetNextRequestNumber() {
		$query = "SELECT IFNULL((SELECT req_number+1 FROM pedidos WHERE req_client = 1 ORDER BY req_number DESC LIMIT 1), 1) as next;";
		$this->assertNotNull ( $result = self::$db->query ( $query ) );
		$this->assertNotNull ( $row = $result->fetch_object () );
		$next = self::$controller->getNextRequestNumber ( 1 );
		$this->assertEquals ( $next, $row->next );
	}
	public function testRegisterRequest() {
		$user = new User();
		$user->id = 1;
		$user->client = 1;
		
		$clientOneDocId = 14467;
		$docs = array($clientOneDocId);
		
		// Registra a requisição
		$requestID = self::$controller->registerDocumentRequest($docs, $user);
		$this->assertTrue($requestID > 0);
		
		// Cancela a requisição
		$request = self::$controller->getRequest($requestID);
		$this->assertTrue(self::$controller->setRequestStatus($request, RequestStatus::CANCELED, $user->getId()));
		
	}
	public function testIsDocFreeToOrder() {
		$this->assertTrue ( self::$controller->isDocFreeToOrder ( 14467 ) );
		$this->assertFalse ( self::$controller->isDocFreeToOrder ( 14466 ) );
	}

	/*
	 * Um cliente tenta requisitar doc de outro
	 */
	public function testCrossClientRequest() {
		$user = new User();
		$user->id = 1;
		$user->client = 1;
		
		$clientOneDocId = 14468;
		$clientTwoDocId = 1;
		$docs = array($clientOneDocId, $clientTwoDocId);

		$requestID = self::$controller->registerDocumentRequest($docs, $user);
		$this->assertEquals($requestID, 0);
	}
}