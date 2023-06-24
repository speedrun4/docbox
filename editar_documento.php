<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/DocumentTypeController.php");
include_once (dirname(__FILE__) . "/core/control/DocumentController.php");
include_once (dirname(__FILE__) . "/core/control/DepartmentController.php");
include_once (dirname(__FILE__) . "/core/control/BoxController.php");
include_once (dirname(__FILE__) . "/core/utils/Utils.php");

use Docbox\control\DepartmentController;
use Docbox\control\DocumentTypeController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use Docbox\control\DocumentController;

$user = getUserLogged();
$doc_id = getReqParam("doc", "int", "get");

if($user == NULL || $user->getClient() <= 0 || $doc_id <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$docController = new DocumentController($db);
$doctypeController = new DocumentTypeController($db);
$departController = new DepartmentController($db);
$boxController = new BoxController($db);

$doc = $docController->getDocumentById($doc_id);
if($doc == NULL || $doc->getClient() != $user->getClient() || $doc->isDead()) {
	header("Location: pesquisa_documentos.php");
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
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Documentos</a></li>
                            <li class="breadcrumb-item"><a href="pesquisa_documentos.php">Listar Documentos</a></li>
                            <li class="breadcrumb-item active">Editar Documento</li>
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
                                <h4 class="card-title">Editar documento</h4>
                                <h6 class="card-subtitle"> Informe os dados do documento </h6>
                                <form id='form-register' class="mt-4" method='post' action='#' novalidate>
	                                <input id="doc_id" name="doc_id" type="hidden" value="<?php echo $doc_id; ?>">
	                        		<input id="box_id" name="box_id" type="hidden" value="<?php echo $doc->getBox()->getId(); ?>">
	                        		<input id="doc_token" name="doc_token" type="hidden">

                                	<div class='row'>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="doc_box">Nº da caixa <span class="text-danger">*</span></label>
			                                        <input id='doc_box' name='doc_box' type="number" class="form-control" placeholder="0" min='1' required data-validation-required-message="Por favor preencha este campo" value="<?php echo $doc->getBox()->getNumber(); ?>">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-4'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                                <label class="control-label">Tipo de documento <span class="text-danger">*</span></label>
		                                            <select id='doc_type' name='doc_type' class="form-control" required data-validation-required-message="Por favor preencha este campo">
		                                                <option value="" selected>Selecione...</option>
		                                                    <?php 
		                                                    $doctypes = $doctypeController->getTypes($user->getClient());
	                                                    foreach ($doctypes as $doctype) {
	                                                    	$selected = $doc->getType()->getId() == $doctype->getId() ? "selected" : "";
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
			                                        <label for="doc_year">Ano <span class="text-danger">*</span></label>
			                                        <input id='doc_year' name='doc_year' type="number" class="form-control" placeholder="0000" min='1900' max='<?= date('Y') ?>' required data-validation-required-message="Por favor preencha este campo" value="<?php echo $doc->getYear(); ?>">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                        <label for="doc_number">Nº documento</label>
		                                        <input id='doc_number' name='doc_number' type="number" class="form-control" placeholder="0" min='1' value="<?php echo $doc->getNumber() > 0 ? $doc->getNumber() : ""; ?>">
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
                                			<div class="form-group">
                                                <label class="control-label">Letra</label>
                                                <select class="form-control custom-select" name='doc_letter'>
                                                	<option value=""></option>
	                                            	<?php 
	                                            		$selected = "";
	                                                    for ($i = 1; $i < 27; $i++) {
	                                                    	$selected = "";
	                                                    	if($doc->getLetter() == chr($i + 64)) $selected = "selected";
	                                                    	echo "<option value='" . chr($i + 96) . "' $selected>&nbsp;&nbsp;&nbsp;" . chr($i + 64) . "</option>";
	                                                    }
	                                                ?>
                                                </select>
                                            </div>
                                		</div>
                                	</div>
                                	<div class='row'>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                        <label for="doc_volume">Volume</label>
		                                        <input id='doc_volume' name='doc_volume' type="number" class="form-control" placeholder="0" min='1' value="<?php echo $doc->getVolume(); ?>">
		                                    </div>
                                		</div>
                                		<div class='col-md-7'>
		                                    <div class="form-group">
		                                        <label for="doc_company">Título</label>
		                                        <input id='doc_company' name='doc_company' type="text" class="form-control typeahead" value="<?= $doc->getCompany() ?>" autocomplete="off">
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="doc_date">Data</label>
		                                        <input id='doc_date' name='doc_date' type="date" class="form-control" max='9999-09-09' value="<?php
		                                            if($doc->getDate() != NULL) {
		                                            	echo $doc->getDate()->format("Y-m-d");
		                                            }
		                                            ?>">
		                                    </div>
                                		</div>
                                	</div>
                                	<div class='row'>
	                                	<div class='col-md-6'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
				                                    <label>Alterar arquivo</label>
				                                    <input id='doc_file' name="doc_file" type="file" class="form-control" accept=".pdf">
		                                    	</div>
			                                </div>
		                                </div>
	                                	<?php if(!empty($doc->getFile())) { ?>
		                            	<div class="col-md-4">
		                            		<div class="form-group">
		                            			<div class='controls'>
				                            		<label for="">Arquivo atual</label>
				                            		<p>
				                            			<a href="<?php echo $doc->getFile(); ?>" target='_blank' class='btn btn-success'> <i class='fa fa-download'></i> Download</a>
				                            			<button id='bt_remove_file' type='button' class='btn btn-warning'> <i class='fa fa-trash'></i> Remover</button>
				                            		</p>
			                            		</div>
		                            		</div>
		                            	</div>
		                            	<?php } ?>
                                	</div>
                                    <button type="submit" class="btn btn-primary float-right"><i class='fa fa-sync'></i> Atualizar</button>
                                	<a id='btDelete' href='#' class="btn btn-danger"><i class='fa fa-trash'></i> Excluir</a>
                                	<a href='pesquisa_documentos.php' class="btn float-right">Cancelar</a>
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
    <script src="js/custom.min.js"></script>
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
    ! function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation()
    }(window, document, jQuery);
    $(function() {
    	blockFormEnter('#form-register,#form-register-box');

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
					        		text:"Registro realizado com sucesso!",
					        		title:"",
					        		type:"success",
					        		onClose: () => {
					        			$("#form-register").submit();
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

    	<?php if(!empty($doc->getFile())) { ?>
		$("#bt_remove_file").click(function() {
			swal({
				title: 'Excluir arquivo',
				text: 'Deseja realmente excluir o arquivo PDF do documento?',
				type: 'question',
				confirmButtonText:"Sim",
				confirmButtonColor: 'red',
				showCancelButton: true,
				cancelButtonText: "Cancelar",
				cancelButtonColor: '#3085d6'
			}).then((result) => {
				if(result.value) {
					swal.showLoading();
					$.get('./core/actions/deleteFile.php', {'doc' : <?= $doc->getId() ?>}, function(data) {
						swal('', 'Arquivo excluído com sucesso!', 'success');
						if(data.ok) {
							swal('', 'Arquivo excluído com sucesso!', 'success').then(function() {
								window.location.reload();
							});
						} else {
							swal('Erro', data.error, 'error');
						}
					}, 'json').fail(function() {
						swal("Erro ao realizar pedido", "Por favor verifique sua conexão com a internet, e tente novamente mais tarde.", "error");
					});
				}
			});
		});
        <?php } ?>
        
        $("#form-register").submit(function(e) {
        	var fileOK = false;

	        if($("#doc_file").val() != "" && $("#doc_file")[0].files.length == 1) {
	        	var fileSizeInMB = parseFloat((($("#doc_file")[0].files[0].size / 1024) / 1024).toFixed(4)); // MB
	        	if(fileSizeInMB <= MAX_UPLOAD_SIZE) {
	        		fileOK = true;
		        	$("#doc_file").parents(".form-group").removeClass("error");
		        } else {
		        	$("#doc_file").parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>O arquivo deve ser menor que " + MAX_UPLOAD_SIZE + "Mb</li><li>");
		        	$("#doc_file").parents(".form-group").addClass("error").removeClass("validate");
		        	e.preventDefault();
		        	return;
			    }
		    }

		    var dateOK = true;

		    if($("#doc_date").val() != "") {
		    	dateOK = false;
	    		var year = $("#doc_date").val().substr(0, 4);
				// They must be in the same year
	    		if(year == $("#doc_year").val()) {
					dateOK = true;
				} else {
					swal("Atenção", "Data e Ano do documento não conferem", "warning");
				}
			}

        	if(dateOK) {
        		swal({
      			  title: 'Deseja realmente atualizar o documento?',
      			  text: "Você não será capaz de reverter essa ação!",
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
					        	$.get("./core/actions/boxExists.php", {box:$("#doc_box").val()}, function(res) {
						        	if(res.ok) {
						        		if(fileOK) {
								        	var _data = new FormData();
						        			_data.append(0, $("#doc_file")[0].files[0]);

						        			$.ajax({
									        	url: './core/actions/updateDocument.php?&files',
									        	type: 'POST',
										        data: _data,
										        cache: false,
										        dataType: 'json',
										        processData: false, // Don't process the files
										        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
										        success: function(data, textStatus, jqXHR) {
											        if(data.ok == true) {
		 									        	$("#doc_token").val(data.token);
										                updateDocument();
												    } else {
										                // Handle errors here
										            	swal("", data.error, "error");
													}
										        },
										        error: function(jqXHR, textStatus, errorThrown) {
										            // Handle errors here
										            swal("", "Erro ao realizar upload dos arquivos", "error");
										        }
									        });
						        		} else {
						        			 updateDocument();
							        	}
							        	
							        } else {// Cadastre a caixa e tente novamente
								        swal.close();
								        // Abre modal de cadastro da caixa
								        $("#box_number").val($("#doc_box").val());
							        	$("#modalRegisterBox").modal();
								    }
						        }, 'json').fail(function() {
						        	swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
							    });
					        }
				        });
      				}
      			});
	        }
    		e.preventDefault();
        });

        $("#btDelete").click(function(e) {
            console.log("Vamos deletar");
	    	swal({
    			title: 'Deseja realmente excluir o documento?',
    			text: "Esta ação removerá o documento de todos os pedidos relacionados",
    			type: 'question',
    			showCancelButton: true,
    			confirmButtonColor: 'red',
    			cancelButtonColor: '#3085d6',
    			confirmButtonText: 'Sim',
    			cancelButtonText: "Não",
    			showLoaderOnConfirm: true
			}).then((result) => {
				if(result.value) {
					$.post("./core/actions/deleteDocument.php", {"doc" : $("#doc_id").val()}, function(data) {
				        	if(data.ok) {
							swal({
								title : "Documento excluído com sucesso!",
								type : "success",
							}).then(function() {
	  			        		window.location.href = "pesquisa_documentos.php";
							});
				        	} else {
								swal(data.error, "", data.type);
				        	}
				        }, 'json').fail(function(xhr, status, error){
				        	swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
	  			    });
				}
			});
		});
    });
    function updateDocument() {
		// Success so call function to process the form
    	$.post('./core/actions/updateDocument.php', $("#form-register").serialize(), function(data) {
        	if(data.ok) {
        		swal({
					title : "Alteração realizada com sucesso!",
					type : "success",
				}).then(function() {
		        		window.location.href = "pesquisa_documentos.php";
				});
        	} else {
        		swal(data.error, "", data.type);
        	}
        }, 'json').fail(function(){
        	swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
	    });
	}
    </script>
    <?php include_once ('common_scripts.php'); ?>
</body>

</html>