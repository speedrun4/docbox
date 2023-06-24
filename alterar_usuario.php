<?php
include_once (dirname ( __FILE__ ) . "/core/model/User.php");
include_once (dirname ( __FILE__ ) . "/core/utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/core/model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/core/control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/core/control/UserController.php");
include_once (dirname ( __FILE__ ) . "/core/control/ClientController.php");
include_once (dirname ( __FILE__ ) . "/core/control/DepartmentController.php");
include_once (dirname ( __FILE__ ) . "/core/control/DocumentTypeController.php");

use Docbox\control\ClientController;
use Docbox\control\DepartmentController;
use Docbox\control\DocumentTypeController;
use Docbox\control\UserController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;
use function Docbox\utils\getReqParam;

$user = getUserLogged();
$user_id = getReqParam("user", "int", "get");
if($user == NULL || $user->getClient() <= 0 || $user->getProfile() != User::USER_ADMIN) {
	header("Location: login.php");
}

$db = new DbConnection();
$doctypeController = new DocumentTypeController($db);
$cliController = new ClientController($db);
$usrController = new UserController($db);
$depController = new DepartmentController($db);

$receivedUser = $usrController->getUserById($user_id);
if($receivedUser == NULL || ($user->getClient() != $receivedUser->getClient() && $receivedUser->getClient() != NULL))
	header("Location: login.php");
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <link href="assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <?php include('head.php'); ?>
</head>

<body class="fix-header card-no-border">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <?php
	        include('header.php');
	        include('aside_menu.php');
        ?>
        
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles">
                    <div class="col-md-6 col-8 align-self-center">
                        <h3 class="text-themecolor m-b-0 m-t-0">Usuários</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="listar_usuarios.php">Usuários</a></li>
                            <li class="breadcrumb-item active">Alterar Usuário</li>
                        </ol>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <!-- row -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Alterar usuário</h4>
                                <h6 class="card-subtitle"> Informe os dados do usuário</h6>
                                <form id='form-register-user' class="mt-4" method='post' action='#' novalidate>
                                	<input name='user' type="hidden" value="<?= $receivedUser->getId() ?>">
                                	<div class='row'>
                                		<div class='col-md-2'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Perfil <span class="text-danger">*</span></label>
		                                            <select id='userprofile' name='userprofile' class="form-control" required data-validation-required-message="Por favor informe o perfil">
		                                                <option value="">Selecione...</option>
	                                                    <option value="1" <?php if($receivedUser->getProfile() == 1) echo "selected"; ?>>Usuário Administrador</option>
		                                            	<option value="2" <?php if($receivedUser->getProfile() == 2) echo "selected"; ?>>Usuário Comum</option>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-4'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="username">Nome completo <span class="text-danger">*</span></label>
			                                        <input id='username' name='username' type="text" class="form-control" placeholder="Nome completo" maxlength='45' required data-validation-required-message="Por favor informe o nome do usuário" autocomplete="off" value="<?php echo $receivedUser->getName(); ?>">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="login">Login <span class="text-danger">*</span></label>
			                                        <input id='login' name='login' type="text" class="form-control" required data-validation-required-message="Por favor informe o login" maxlength="16" autocomplete="off" value="<?php echo $receivedUser->getLogin(); ?>">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="password">Senha</label>
			                                        <input id='password' name='password' type="password" class="form-control">
		                                        </div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="confpassword">Confirme a senha</label>
			                                        <input id='confpassword' name='confpassword' type="password" class="form-control">
		                                        </div>
		                                    </div>
                                		</div>
                                	</div>
                                	<div class='row'>
                                		<div class='col-md-6'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="email">Email <span class="text-danger">*</span></label>
			                                        <input id='email' name='email' type="email" class="form-control" required data-validation-required-message="Por favor informe o email" data-validation-email-message="Por favor informe um email válido" autocomplete="off" value="<?php echo $receivedUser->getEmail(); ?>">
		                                        </div>
		                                    </div>
                                		</div>
                                		<div class='col-md-6'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Departamentos <span class="text-danger">*</span></label> <select
														name='departments[]' class="select2 mb-2 select2-multiple"
														style="width: 100%" multiple="multiple"
														data-placeholder="Selecione"
														data-validation-callback-callback="validateDepartments">
														<option value="" disabled>Selecione...</option>
	                                                    <?php
					                                    	$departments = $depController->getDepartments($user->getClient());
					                                    	$usrDepartments = $usrController->getUserDepartments($receivedUser->getId());
			
					                                    	foreach($departments as $department) {
					                                    		$selected = "";
					                                    		foreach($usrDepartments as $ud) {
					                                    			if($ud->getId() == $department->getId()) $selected = "selected";
					                                    		}
					                                    		echo "<option value='" . $department->getId() . "' $selected>" . $department->getName() . "</option>";
					                                    	}
				                                    	?>
		                                            </select>
												</div>
	                                   		</div>
                                    	</div>
                                	</div>
                                	<div class='row'>
                                		<div class='col-md-12'>
		                                    <button id='btDelete' type="button" class="btn btn-danger">Excluir</button>
		                                    <button type="submit" class="btn btn-primary float-right"><i class='fa fa-sync'></i> Atualizar</button>
                                		</div>
                                	</div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Page Content -->
                <!-- ============================================================== -->
                <?php include('right_sidebar.php'); ?>
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <?php include('footer.php'); ?>
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="js/jquery.slimscroll.js"></script>
    <!--Wave Effects -->
    <script src="js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="js/sidebarmenu.js"></script>
    <!--stickey kit -->
    <script src="assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <!--Custom JavaScript -->
    <script src="js/custom.min.js"></script>
    <!-- Validation -->
    <script src="js/altered_validation.js?v=1.0"></script>
    <!-- Sweet-Alert  -->
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="assets/plugins/sweetalert/jquery.sweet-alert.custom.js"></script>
    <!--Custom JavaScript -->
    <script src="js/session.min.js"></script>
    <script src="js/app.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <script src="js/jasny-bootstrap.js"></script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    <script src="js/typeahead.js/typeahead.bundle.js"></script>
    <script src="assets/plugins/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script src="assets/plugins/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="assets/plugins/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
    <script type="text/javascript">
    ! function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation()
    }(window, document, jQuery);

    $(function() {
    	$(".select2").select2();
    	
    	blockFormEnter('#form-register,#form-register-box');

    	$("#form-register-user").submit(function(e) {
			var performSubmit = true;

        	if($("#password").val() != "" || $("#confpassword").val() != "") {
        		performSubmit = false;
            	if($("#password").val() == $("#confpassword").val()) {
    	        	performSubmit = isPasswordValid($("#password"));
            	} else {
            		showErrorBlock($("#password"), "As senhas não conferem");
            		showErrorBlock($("#confpassword"), "As senhas não conferem");
    	        }
	        }

        	/** TODO Validação async de login */
        	if(performSubmit) {
        		swal({
		        	title: "Aguarde...",
		        	showConfirmButton: false,
		        	allowOutsideClick: false,
		        	onOpen: function() {
		        		swal.showLoading();
	        			// Success so call function to process the form
			        	$.post('./core/actions/updateUser.php', $("#form-register-user").serialize(), function(data) {
				        	if(data.ok) {
				        		swal({
									title : "Alteração realizada com sucesso!",
									type : "success",
								}).then(function() {
		  			        		window.location.href = "listar_usuarios.php";
								});
				        	} else {
				        		swal(data.error, "", data.type);
				        	}
				        }, 'json').fail(function() {
				        	swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
					    });
			        }
		        });
	       	}
        	e.preventDefault();
	    });

    	$("#btDelete").click(function() {
	    	swal({
        		type : 'question',
        		title: 'Excluir usuário',
        		text: 'Deseja realmente excluir o usuário?',
        		showCancelButton: true,
        		confirmButtonColor: '#3085d6',
    			cancelButtonColor: '#aaa',
    			confirmButtonText: 'Sim',
    			cancelButtonText: "Não",
    			allowOutsideClick: false
        	}).then((result) => {
            	if(result.value) {
				    $.post('./core/actions/deleteUser.php', {user:<?php echo $user_id; ?>}, function(data) {
					    swal({
	    				    title:data.message,
	    				    text:"",
	    				    type: data.type
	    				}).then(() => {
	        				if(data.ok) window.location = "listar_usuarios.php";
	        			});
					}, 'json').fail(function() {
			        	swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
				    });
            	}
        	});
		});
    });
	function validateDepartments($el, value, callback) {
		callback({
	      value: value,
	      valid: $("#userprofile").val() == <?= User::USER_ADMIN ?> || value.length > 0,
	      message: "Por favor informe o(s) departamento(s)"
	    });
	}
    </script>
    <?php include_once ('common_scripts.php'); ?>
</body>

</html>