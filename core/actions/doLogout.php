<?php
namespace Docbox\actions;

/**
 * Arquivo responsável pelo logout do usuário
 */
use function Docbox\control\doLogout;

include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../config/DbConfiguration.php");

doLogout ();

header ( "Location: ../../index.php" );