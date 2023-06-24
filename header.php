<?php
include_once (dirname(__FILE__) . "/core/control/ClientController.php");

use Docbox\control\ClientController;

$cliController = new ClientController($db);
$client = NULL;
if($user != NULL) {
    $client = $cliController->getClient($user->getClient());
} else {
    header("Location: login.php");
    exit;
}
?>
<!-- ============================================================== -->
<!-- Topbar header - style you can find in pages.scss -->
<!-- ============================================================== -->
        <header class="topbar">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                <!-- ============================================================== -->
                <!-- Logo -->
                <!-- ============================================================== -->
                <div class="navbar-header">
                    <a class="navbar-brand" href="index.php">
                        <!-- Logo icon -->
                        <b>
                            <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                            <!-- Dark Logo icon -->
                            <img src="assets/images/docbox_logo_icon.png" alt="homepage" class="dark-logo" width='34'/>
                            <!-- Light Logo icon -->
                            <img src="assets/images/docbox_logo_icon.png" alt="homepage" class="light-logo" width='34'/>
                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span>
                         <!-- dark Logo text -->
                         <img src="assets/images/logo_light_text.png" alt="homepage" class="dark-logo" />
                         <!-- Light Logo text -->    
                         <img src="assets/images/logo_light_text.png" class="light-logo" alt="homepage" /></span> </a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <!-- This is  -->
                    <div class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </div>
                    <div class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="icon-arrow-left-circle"></i></a> </div>
                    <div class="navbar-nav mr-auto mt-md-0 " style="flex-grow: 1">
                        <div class="" style="flex:1;">
                        	<span class="navbar-nav-title text-white">
                        		<?php if($client != NULL) echo $client->getName(); ?>
                        	</span>
                        </div>
                        
                    </div>
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav my-lg-0">
                    	<?php if ($user->isAdmin()) { ?>
                    	<li id="request_notifications" class="nav-item dropdown"></li>
                    	<?php } ?>
                        <li class="nav-item">
	                        <a style='' class="m-t-15 nav-link dropdown-toggle text-muted right-side-toggle waves-effect waves-dark btn-info btn-circle btn-sm" href="" data-toggle="dropdown" aria-expanded="false"><i class="ti-settings text-white" style="margin-left: -2px; margin-top: -1px"></i></a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="assets/images/avatar.png" alt="user" class="profile-pic" /></a>
                            <div class="dropdown-menu dropdown-menu-right animated flipInY">
                                <ul class="dropdown-user">
                                    <li>
                                        <div class="dw-user-box">
                                            <div class="u-img"><img src="assets/images/avatar.png" alt="user"></div>
                                            <div class="u-text">
                                                <h4><?= $user->getName() ?></h4>
                                                <p class="text-muted"><?= $user->getLogin() ?></p><!--a href="#" class="btn btn-rounded btn-danger btn-sm">View Profile</a--></div>
                                        </div>
                                    </li>
                                    <!--li role="separator" class="divider"></li>
                                    <li><a href="#"><i class="ti-user"></i> My Profile</a></li>
                                    <li><a href="#"><i class="ti-wallet"></i> My Balance</a></li>
                                    <li><a href="#"><i class="ti-email"></i> Inbox</a></li>
                                    <li role="separator" class="divider"></li>
                                    <li><a href="#"><i class="ti-settings"></i> Account Setting</a></li-->
                                    <li role="separator" class="divider"></li>
                                    <li><a href="core/actions/doLogout.php"><i class="fa fa-power-off"></i> Sair</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
<!-- ============================================================== -->
<!-- End Topbar header -->
<!-- ============================================================== -->