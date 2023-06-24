<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/ClientController.php");
include_once (dirname(__FILE__) . "/core/control/DepartmentController.php");
include_once (dirname(__FILE__) . "/core/model/User.php");

use Docbox\control\ClientController;
use Docbox\control\DepartmentController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0 || $user->getProfile() != User::USER_ADMIN) {
	header("Location: login.php");
}

$db = new DbConnection();
$cliController = new ClientController($db);
$depController = new DepartmentController($db);
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
                            <li class="breadcrumb-item active">Cadastrar Usuário</li>
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
                                <h4 class="card-title">Cadastrar usuário</h4>
                                <h6 class="card-subtitle"> Informe os dados do usuário</h6>
                                <form id='form-register-user' class="mt-4" method='post' action='#' autocomplete="off" novalidate>
                                	<div class='row'>
                                		<div class='col-md-2'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Perfil <span class="text-danger">*</span></label>
		                                            <select id='userprofile' name='userprofile' class="form-control" required data-validation-required-message="Por favor informe o perfil">
		                                                <option value="">Selecione...</option>
		                                                <option value="1">Usuário Administrador</option>
	                                                    <option value="2">Usuário Comum</option>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-4'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="username">Nome completo <span class="text-danger">*</span></label>
			                                        <input id='username' name='username' type="text" class="form-control" placeholder="Nome completo" value='' required data-validation-required-message="Por favor informe o nome do usuário">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="login">Login <span class="text-danger">*</span></label>
			                                        <input id='login' name='login' type="text" class="form-control" required data-validation-required-message="Por favor informe o login" maxlength='16'>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="password">Senha <span class="text-danger">*</span></label>
			                                        <input id='password' name='password' type="password" class="form-control" required data-validation-required-message="Por favor informe a senha">
		                                        </div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="confpassword">Confirme a senha <span class="text-danger">*</span></label>
			                                        <input id='confpassword' name='confpassword' type="password" class="form-control" required data-validation-required-message="Por favor confirme a senha">
		                                        </div>
		                                    </div>
                                		</div>
                                	</div>
                                	<div class='row'>
                                		<div class='col-md-6'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="email">Email <span class="text-danger">*</span></label>
			                                        <input id='email' name='email' type="email" class="form-control" required data-validation-required-message="Por favor informe o email" data-validation-email-message="Por favor informe um email válido">
		                                        </div>
		                                    </div>
                                		</div>
                                		<div class='col-md-6'>
	                                		<div class="form-group">
		                                        <div class="controls">
													<label class="control-label">Departamentos <span
														class="text-danger">*</span></label>
													<select id="txt_departments" name='departments[]' class="select2 mb-2 select2-multiple"
														style="width: 100%" multiple="multiple"
														data-placeholder="Selecione"
														data-validation-callback-callback="validateDepartments">
														<option value="" disabled>Selecione...</option>
	                                                    <?php
					                                    	$departments = $depController->getDepartments($user->getClient());
					                                    	foreach($departments as $department) {
					                                    		echo "<option value='" . $department->getId() . "'>" . $department->getName() . "</option>";
					                                    	}
				                                    	?>
		                                            </select>
												</div>
	                                   		</div>
                                    	</div>
                                	</div>
                                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End PAge Content -->
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
        	if($("#password").val() == $("#confpassword").val()) {
	        	performSubmit = performSubmit && isPasswordValid($("#password"));
        	} else {
        		performSubmit = false;
        		showErrorBlock($("#password"), "As senhas não conferem");
        		showErrorBlock($("#confpassword"), "As senhas não conferem");
	        }

        	/** TODO Login async validation */
        	if(performSubmit) {
	        	swal({
			       	title: "Aguarde...",
			       	showConfirmButton: false,
			       	allowOutsideClick: false,
			       	onOpen: function() {
		        		swal.showLoading();
		        		// Success so call function to process the form
				       	$.post('./core/actions/registerUser.php', $("#form-register-user").serialize(), function(data) {
				        	if(data.ok) {
				        		swal({
									title : "Cadastro realizado com sucesso!",
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
        	} else {
        	}
        	e.preventDefault();
	    });
    });
    function registerDocument() {
		// Success so call function to process the form
    	$.post('./core/actions/registerDocument.php', $("#form-register").serialize(), function(data) {
        	if(data.ok) {
	        	var doc_box = $("#doc_box").val();
        		var doc_year = $("#doc_year").val();
        		var doc_type = $("#doc_type").val();

        		$("#form-register")[0].reset();

        		$("#doc_year").val(doc_year);
        		$("#doc_box").val(doc_box);
        		$("#doc_type").val(doc_type);

        		swal("", "Cadastro realizado com sucesso!", data.type);
			} else {
				swal(data.error, "", data.type);
			}
    	}, 'json').fail(function() {
			swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
		});
	}
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