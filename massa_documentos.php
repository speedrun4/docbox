<?php
include_once (dirname ( __FILE__ ) . "/core/control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/core/control/BoxController.php");
include_once (dirname ( __FILE__ ) . "/core/control/DocumentController.php");
include_once (dirname ( __FILE__ ) . "/core/control/DocumentTypeController.php");

use Docbox\control\DocumentController;

$user = getUserLogged ();
if ($user == NULL || $user->getClient () <= 0) {
	exit ();
}

$db = new DbConnection ();
$documentController = new DocumentController ( $db );
$dctController = new DocumentTypeController($db);
$boxController = new BoxController ( $db );

$types = $dctController->getTypes();

if(!$boxController->boxExists(1, 1)) {
    try {
        if(!$boxController->busyLocation(1, 1, 1)) {
            if($boxController->registerBox(1, 1, 1, 1, 1)) {
                for($i = 0; $i < 100; $i++) {
                	$box= new Box();
                	$box->setId(1);
                
                	$document = new Document ();
                	$document->setClient ( 1 );
                	$document->setBox ( $box );
                	$document->setType ( $types[rand(0, count($types) - 1 )]->getId() );
                	$document->setYear ( rand(2000, 2018) );
                	$document->setNumber ( rand(1, 999) );
                	$document->setLetter ( NULL );
                	$document->setVolume ( rand(0, 1));
                	
                	if (! $documentController->docExists ( $document )) {
                		$id = $documentController->insertDocument ( $document, $user->getId () );
                		if ($id > 0) {
                			echo "<li>ok";
                		}
                	} else {
                		echo "<li style='color: red'>erro";
                	}
                }
            }
        }
    } catch (Exception $e) {
        // echo 'Exceção capturada: ',  $e->getMessage(), "\n";
    }
}