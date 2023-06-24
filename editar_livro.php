<?php
    include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
    include_once (dirname(__FILE__) . "/core/control/UserSession.php");
	include_once (dirname(__FILE__) . "/core/control/ClientController.php");
	include_once (dirname(__FILE__) . "/core/control/DocumentTypeController.php");
	include_once (dirname(__FILE__) . "/core/control/DepartmentController.php");
	include_once (dirname(__FILE__) . "/core/control/BookController.php");
	include_once (dirname(__FILE__) . "/core/utils/Utils.php");
	include_once (dirname(__FILE__) . "/core/model/User.php");
	
    use Docbox\control\ClientController;
	use Docbox\control\DepartmentController;
	use Docbox\control\DocumentTypeController;
	use function Docbox\control\getUserLogged;
	use Docbox\model\DbConnection;
	use Docbox\model\User;
	use function Docbox\utils\getReqParam;
    
	$user = getUserLogged();
	$book_id = getReqParam("book", "int", "get");

	if($user == NULL || $user->getClient() <= 0 || $user->getProfile() != User::USER_ADMIN || $book_id <= 0) {
		header("Location: login.php");
	}

	$db = new DbConnection();
	$doctypeController = new DocumentTypeController($db);
	$cliController = new ClientController($db);
	$departController = new DepartmentController($db);
	$bookController = new BookController($db);

	$book = $bookController->getBookById($book_id);
	if($book == NULL || $book->getClient() != $user->getClient()) {
	   header("Location: listar_livros.php");
    }
?>
<!DOCTYPE html>
<html lang="pt">

<head>
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
                        <h3 class="text-themecolor m-b-0 m-t-0">Documentos</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="listar_livros.php">Livros</a></li>
                            <li class="breadcrumb-item active">Alterar Livro</li>
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
                                <h4 class="card-title">Alterar livro</h4>
                                <h6 class="card-subtitle">Informe os dados do livro</h6>
                                <form id='form-register' class="mt-4" method='post' action='#' novalidate>
                                	<input id='liv_id' name='liv_id' type='hidden' value='<?= $book->getId() ?>'>
                                	<input name='box_id' type='hidden' value='<?= $book->getBox()->getId() ?>'>
                                	<div class='row'>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="liv_box">Nº da caixa <span class="text-danger">*</span></label>
			                                        <input id='liv_box' name='liv_box' type="number" class="form-control" placeholder="0" min='1' required data-validation-required-message="Por favor preencha este campo" value="<?= $book->getBox()->getNumber() ?>">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Tipo de documento <span class="text-danger">*</span></label>
		                                            <select id='liv_type' name='liv_type' class="form-control" required data-validation-required-message="Por favor preencha este campo">
		                                                <option value="" selected>Selecione...</option>
		                                                    <?php
		                                                    $doctypes = $doctypeController->getTypes($user->getClient());
		                                                    foreach ($doctypes as $doctype) {
		                                                        $selected = $book->getType()->getId() == $doctype->getId() ? "selected" : "";
		                                                        echo "<option value='" . $doctype->getId() . "' $selected>" . $doctype->getDescription() . "</option>";
		                                                    }
		                                                    ?>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="liv_year">Ano <span class="text-danger">*</span></label>
			                                        <input id='liv_year' name='liv_year' type="number" class="form-control" placeholder="0000" min='1900' max='<?= date('Y') ?>' required data-validation-required-message="Por favor preencha este campo" value="<?= $book->getYear() ?>"> 
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
    		                                        <label for="liv_num_from">Nº inicial <span class="text-danger">*</span></label>
    		                                        <input id='liv_num_from' name='liv_num_from' type="number" class="form-control" placeholder="0" min='1' required required data-validation-required-message="Por favor preencha este campo" value='<?= $book->getNumFrom() ?>'>
		                                        </div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
    		                                        <label for="liv_num_to">Nº final <span class="text-danger">*</span></label>
    		                                        <input id='liv_num_to' name='liv_num_to' type="number" class="form-control" placeholder="0" min='1' required required data-validation-required-message="Por favor preencha este campo" value='<?= $book->getNumTo() ?>'>
		                                       </div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                        <label for="liv_volume">Volume</label>
		                                        <input id='liv_volume' name='liv_volume' type="number" class="form-control" placeholder="0" min='1' value='<?= $book->getVolume() ?>'>
		                                    </div>
                                		</div>
                                	</div>
                                    <button type="submit" class="btn btn-primary float-right"><i class='fa fa-save'></i> Salvar</button>
                                    <button id='btDelete' type="button" class="btn btn-danger"><i class='fa fa-trash'></i> Excluir</button>
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
    <?php include("modal_register_box.php"); ?>
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
    <script src="js/custom.js"></script>
    <!-- Validation -->
    <script src="js/validation.js"></script>
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
    <script type="text/javascript">
    !function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation()
    }(window, document, jQuery);
    $(function() {
    	blockFormEnter('#form-register,#form-register-box');

		var formRegisterSubmit = function(e) {
    		swal({
    			  title: 'Deseja realmente alterar o livro?',
    			  // text: "Você não será capaz de reverter essa ação!",
    			  type: 'warning',
    			  showCancelButton: true,
    			  confirmButtonColor: '#d33',
    			  cancelButtonColor: '#3085d6',
    			  confirmButtonText: 'Sim',
    			  cancelButtonText: "Não",
    			  showLoaderOnConfirm: true
    			}).then((result) => {
        			if(result.value) {
			        	swal({
				        	title: "Aguarde...",
				        	showConfirmButton: false,
				        	allowOutsideClick: false,
				        	onOpen: function() {
				        		swal.showLoading();
					        	$.get("./core/actions/boxExists.php", {box:$("#liv_box").val()}, function(res) {
						        	if(res.ok) {
						        		updateBook();
							        } else {// Cadastre a caixa e tente novamente
								        swal.close();
								        // Abre modal de cadastro da caixa
								        $("#box_number").val($("#liv_box").val());
							        	$("#modalRegisterBox").modal();
								    }
						        }, 'json').fail(function() {
						        	swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
							    });
					        }
					    });
        			}
        		});
    		e.preventDefault();
        };

        $("#form-register-box").submit(function(e) {
        	$("#modalRegisterBox").modal('hide');
			swal({
				title: "Aguarde...",
	        	showConfirmButton: false,
	        	allowOutsideClick: false,
	        	onOpen: function() {
	        		swal.showLoading();
	        		// Registra a caixa
	        		$.post('./core/actions/registerBox.php', $("#form-register-box").serialize(), function(res) {
		        		if(res.ok) {
			        		swal({
			        			title : "Registro realizado com sucesso!",
			        			text : "",
				        		type:"success",
				        		onClose: () => {
				        			formRegisterSubmit();
				        		}
				        	});
			        	} else {
			        		swal("Erro ao realizar a operação", res.message, res.type);
				        }
	        		}, 'json').fail(function(){
	        			swal("Erro ao realizar a operação", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
	        		});
		        }
			});
			e.preventDefault();
		});

        $("#btDelete").click(function() {
	    	swal({
    			title: 'Deseja realmente excluir o livro?',
    			text: "Esta ação removerá o livro de todos os pedidos relacionados",
    			type: 'question',
    			showCancelButton: true,
    			confirmButtonColor: 'red',
    			cancelButtonColor: '#3085d6',
    			confirmButtonText: 'Sim',
    			cancelButtonText: "Não",
    			showLoaderOnConfirm: true
			}).then((result) => {
				if(result.value) {
					$.post("./core/actions/deleteBook.php", {"book" : $("#liv_id").val()},
						function(data) {
				        	if(data.ok) {
    							swal({
    								title : "Livro excluído com sucesso!",
    								type : "success",
    							}).then(function() {
    	  			        		window.location.href = "listar_livros.php";
    							});
				        	} else {
								swal(data.error, "", data.type);
				        	}
				        }, 'json').fail(function(xhr, status, error) {
				        	swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
	  			    });
				}
			});
		});

    	$("#form-register").submit(formRegisterSubmit);
    });

    function updateBook() {
		// Success so call function to process the form
    	$.post('./core/actions/updateBook.php', $("#form-register").serialize(), function(data) {
        	if(data.ok) {
        		swal("Alteração realizada com sucesso!", "", "success");
			} else {
				swal(data.error, "", data.type);
			}
    	}, 'json').fail(function() {
			swal("Erro ao realizar solicitação", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
		});
	}
    </script>
</body>

</html>