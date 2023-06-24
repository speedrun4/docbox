<?php
include_once(dirname(__FILE__) . "/../core/model/User.php");
include_once(dirname(__FILE__) . "/../core/model/RequestStatus.php");
include_once(dirname(__FILE__) . "/../core/model/DbConnection.php");
include_once(dirname(__FILE__) . "/../core/control/RequestController.php");
include_once(dirname(__FILE__) . "/../core/control/DevolutionController.php");

use Docbox\control\DevolutionController;
use Docbox\control\RequestController;
use Docbox\model\RequestStatus;
use PHPUnit\Framework\TestCase;
use Docbox\model\DbConnection;
use Docbox\model\User;

class DevolutionControllerTest extends TestCase {
	/**
	 * @var DevolutionController
	 */
	static protected $controller;
	/**
	 * @var DbConnection
	 */
	static protected $db;

	public static function setUpBeforeClass():void {
		self::$db = new DbConnection();
		self::$controller = new DevolutionController(self::$db);
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

	public function testGetDevolutionById() {
		$devolution = self::$controller->getDevolutionById(2);
		$this->assertNotNull($devolution);
	}
	
	public function testGetDocumentsFromDevolution() {
		$devolution = self::$controller->getDevolutionById(1);
		$docs = self::$controller->getDocumentsFromDevolution($devolution->getId());
		$this->assertGreaterThan(0, count($docs));
	}
	
	public function testGetNextDevolutionNumber() {
		$number = 0;
		$query = "SELECT COALESCE(MAX(ret_number), 0) + 1 AS number FROM devolucoes";
		if ($result = self::$db->query ( $query )) {
			if ($row = $result->fetch_object ()) {
				$number = $row->number;
			}
		}
		$this->assertEquals($number, self::$controller->getNextDevolutionNumber());
	}

	public function testRegisterDocumentDevolution() {
		$user = new User();
		$user->id = 1;
		$user->client = 1;

		$docs = array(800, 801);

		// Cria um pedido de documentos
		$reqController = new RequestController(self::$db);

		$reqId = $reqController->registerDocumentRequest($docs, $user);
		$request = $reqController->getRequest($reqId);
		$this->assertNotNull($request);
		$reqController->setRequestStatus($request, RequestStatus::ATTENDEND, $user->getId());
		
		$docsToDevolve = $reqController->getDevolutionAvailableDocumentIds($reqId);

		// Devolve somente um documento que NÃO está no pedido
		$devolutionId = self::$controller->registerPartialDocumentDevolution(array(0), $user->getId());
		$this->assertTrue($devolutionId == 0);

		// Devolve um documento do pedido
		$devolutionId = self::$controller->registerPartialDocumentDevolution(array($docsToDevolve[0]), $user->getId());
		$this->assertGreaterThan(0, $devolutionId);

		// Finaliza a devolução
		$devolution = self::$controller->getDevolutionById($devolutionId);
		$this->assertTrue(self::$controller->finishDocumentDevolution($devolution, "0dfce7585fb94bd6f168fe6e33fb84b4c4ffffa5.png", $user->getId()));

		// Tenta devolver o mesmo documento
		$devolutionId = self::$controller->registerPartialDocumentDevolution(array($docsToDevolve[0]), $user->getId());
		$this->assertTrue($devolutionId == 0);

		// Tenta finalizar a mesma devolução
		$this->assertFalse(self::$controller->finishDocumentDevolution($devolution, "0dfce7585fb94bd6f168fe6e33fb84b4c4ffffa5.png", $user->getId()));
		
		// TODO É possível realizar pedido de documento com a documento que foi devolvida?

		// Devolve o segundo documento, e finaliza a devolução
		$devolutionId = self::$controller->registerPartialDocumentDevolution(array($docsToDevolve[1]), $user->getId());
		$this->assertGreaterThan(0, $devolutionId);
		$devolution = self::$controller->getDevolutionById($devolutionId);
		$this->assertTrue(self::$controller->finishDocumentDevolution($devolution, "0dfce7585fb94bd6f168fe6e33fb84b4c4ffffa5.png", $user->getId()));

		// Pedido deve estar finalizado...
		$request = $reqController->getRequest($reqId);
		$this->assertEquals($request->getStatus(), RequestStatus::COMPLETED);
	}

	public function testRegisterBoxDevolution() {
	    $user = new User();
	    $user->id = 1;
	    $user->client = 1;

	    $boxes = array(83, 84);

	    // Cria um pedido de caixas
	    $reqController = new RequestController(self::$db);

	    $reqId = $reqController->registerBoxRequest($boxes, $user);
	    $request = $reqController->getRequest($reqId);
	    $this->assertNotNull($request);
	    
	    $this->assertTrue($reqController->setRequestStatus($request, RequestStatus::ATTENDEND, $user->getId()));
	    
	    $boxes2Devolve = $reqController->getDevolutionAvailableBoxIds($reqId);
	    
	    // Devolve somente um caixas que NÃO está no pedido
	    $devolutionId = self::$controller->registerPartialBoxDevolution(array(0), $user->getId());
	    $this->assertTrue($devolutionId == 0);
	    
	    // Devolve um caixas do pedido
	    $devolutionId = self::$controller->registerPartialBoxDevolution(array($boxes2Devolve[0]), $user->getId());
	    $this->assertGreaterThan(0, $devolutionId);

	    // Finaliza a devolução
	    $devolution = self::$controller->getDevolutionById($devolutionId);
	    $this->assertTrue(self::$controller->finishBoxDevolution($devolution, "0dfce7585fb94bd6f168fe6e33fb84b4c4ffffa5.png", $user->getId()));

	    // Tenta devolver o mesmo caixas
	    $devolutionId = self::$controller->registerPartialBoxDevolution(array($boxes2Devolve[0]), $user->getId());
	    $this->assertTrue($devolutionId == 0);
	    
	    // Tenta finalizar a mesma devolução
	    $this->assertFalse(self::$controller->finishBoxDevolution($devolution, "0dfce7585fb94bd6f168fe6e33fb84b4c4ffffa5.png", $user->getId()));
	    
	    // TODO É possível realizar pedido de caixa com a caixa que foi devolvida?
	    $reqId2 = $reqController->registerBoxRequest(array($boxes[0]), $user);
	    $this->assertGreaterThan(0, $reqId2);
	    $request2 = $reqController->getRequest($reqId2);
	    $reqController->setRequestStatus($request2, RequestStatus::CANCELED, $user->getId());
	    
	    // Devolve o segundo caixas, e finaliza a devolução
	    $devolutionId = self::$controller->registerPartialBoxDevolution(array($boxes2Devolve[1]), $user->getId());
	    $this->assertGreaterThan(0, $devolutionId);
	    $devolution = self::$controller->getDevolutionById($devolutionId);
	    $this->assertTrue(self::$controller->finishBoxDevolution($devolution, "0dfce7585fb94bd6f168fe6e33fb84b4c4ffffa5.png", $user->getId()));

	    // Pedido deve estar finalizado...
	    $request = $reqController->getRequest($reqId);
	    $this->assertEquals($request->getStatus(), RequestStatus::COMPLETED);
	}
}