<?php
	include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
	include_once (dirname(__FILE__) . "/core/control/UserSession.php");
	include_once (dirname(__FILE__) . "/core/control/ClientController.php");
	include_once (dirname(__FILE__) . "/core/control/DocumentTypeController.php");
	include_once (dirname(__FILE__) . "/core/control/DepartmentController.php");

	use Docbox\control\ClientController;
	use Docbox\control\DepartmentController;
	use Docbox\control\DocumentTypeController;
	use function Docbox\control\getUserLogged;
	use Docbox\model\DbConnection;
	use Docbox\model\User;
	
	$user = getUserLogged();
	if($user == NULL || $user->getClient() <= 0 || $user->getProfile() != User::USER_ADMIN) {
		header("Location: login.php");
	}

	$db = new DbConnection();
	$doctypeController = new DocumentTypeController($db);
	$cliController = new ClientController($db);
	$departController = new DepartmentController($db);
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
                            <li class="breadcrumb-item"><a href="pesquisa_documentos.php">Documentos</a></li>
                            <li class="breadcrumb-item active">Cadastrar Documentos</li>
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
                                <h4 class="card-title">Cadastrar documento</h4>
                                <h6 class="card-subtitle"> Informe os dados do documento </h6>
                                <form id='form-register' class="mt-4" method='post' action='#' novalidate>
                                	<input id="doc_token" name="doc_token" type="hidden">
                                	<div class='row'>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="doc_box">Nº da caixa <span class="text-danger">*</span></label>
			                                        <input id='doc_box' name='doc_box' type="number" class="form-control" placeholder="0" min='1' value='' required data-validation-required-message="Por favor preencha este campo" autofocus="autofocus">
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
		                                                    	echo "<option value='" . $doctype->getId() . "'>" . $doctype->getDescription() . "</option>";
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
			                                        <input id='doc_year' name='doc_year' type="number" class="form-control" placeholder="0000" min='1900' max='<?= date('Y') ?>' required data-validation-required-message="Por favor preencha este campo">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class="controls">
    		                                        <label for="doc_number">Nº documento</label>
    		                                        <input id='doc_number' name='doc_number' type="number" class="form-control" placeholder="0" min='1'>
		                                        </div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
                                			<div class="form-group">
                                                <label class="control-label">Letra</label>
                                                <select id='doc_letter' class="form-control custom-select" name='doc_letter'>
                                                	<option value=""></option>
	                                            	<?php
														for ($i = 1; $i < 27; $i++) {
															echo "<option value='" . chr($i + 96) . "'>&nbsp;&nbsp;&nbsp;" . chr($i + 64) . "</option>";
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
		                                        <input id='doc_volume' name='doc_volume' type="number" class="form-control" placeholder="0" min='1'>
		                                    </div>
                                		</div>
                                		<div class='col-md-7'>
		                                    <div class="form-group">
		                                        <label for="doc_company">Título</label>
		                                        <input id='doc_company' name='doc_company' type="text" class="form-control typeahead" autocomplete="off">
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="doc_date">Data</label>
		                                        <input id='doc_date' name='doc_date' type="date" class="form-control" max='9999-09-09'>
		                                    </div>
                                		</div>
                                	</div>
                                    <div class="form-group">
                                    	<div class='controls'>
		                                    <label>Arquivo</label>
		                                    <input id='doc_file' name="doc_file" type="file" class="form-control" accept='.pdf'>
                                    	</div>
	                                </div>
	                                <div class='row'>
                                		<div class='col-md-2 offset-md-10'>
                                			 <div class="form-group">
                                                <div class="custom-control custom-checkbox mr-sm-2">
                                                    <input type="checkbox" class="custom-control-input" id="sealed" name='sealed' value="on">
                                                    <label class="custom-control-label" for="sealed">Selar caixa</label>
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
    <script src="js/typeahead.js/typeahead.bundle.js"></script>
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
    <script type="text/javascript">
    !function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation();
    }(window, document, jQuery);
    $(function() {
    	blockFormEnter('#form-register,#form-register-box');

		var formRegisterSubmit = function(e) {
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
	    		var year = $("#doc_date").val().substr(0,4);
				// They must be in the same year
	    		if(year == $("#doc_year").val()) {
					dateOK = true;
				} else {
					swal("", "Data e Ano do documento não conferem", "warning");
				}
			}

        	if(dateOK) {
        		var yearDiffersConfirmation = false;
    		    var lastYearOfBox =  window.localStorage.getItem('box_' + $("#doc_box").val());

                if(lastYearOfBox) {
                    if(lastYearOfBox != $("#doc_year").val()) {
                		yearDiffersConfirmation = true;
                	}
                } else {
                	window.localStorage.setItem('box_' + $("#doc_box").val(), $("#doc_year").val());
                }

                var sealBoxMsg = '<div class="form-group"><div class="custom-control custom-checkbox mr-sm-2">' +
        		"<input type='checkbox' class='custom-control-input' id='sealBoxCheck' value='on' onchange='changeSealBox(this)'>" +
        		'<label class="custom-control-label" for="sealBoxCheck">Selar caixa após o cadastro</label></div></div>';

        		var emptyDocNumber = $("#doc_number").val() == 0 ? "<b><span class='text-danger'>* ATENÇÃO - Número do documento zerado!</span></b>" : "";

        		var yearDiffers = yearDiffersConfirmation ? "<p>O ano do documento difere do último cadastrado. Confirma a alteração?</p>" + 
                		'<div class="form-group"><div class="custom-control custom-checkbox mr-sm-2">' +
                		"<input type='checkbox' class='custom-control-input' id='differsConfirm' value='on'>" +
                		'<label class="custom-control-label" for="differsConfirm">Confirmo</label></div></div>' : "";
                var htmlMessage = emptyDocNumber + yearDiffers + sealBoxMsg;
        		swal({
        			  title: 'Deseja realmente cadastrar o documento?',
        			  html: htmlMessage,
        			  type: 'warning',
        			  showCancelButton: true,
        			  confirmButtonColor: '#d33',
        			  cancelButtonColor: '#3085d6',
        			  confirmButtonText: 'Sim',
        			  cancelButtonText: "Não",
        			  showLoaderOnConfirm: true
        			}).then((result) => {
            			if(result.value) {
                			if(yearDiffersConfirmation) {
                    			// O checkbox precisa estar marcado
                    			if(!$("#differsConfirm").is(":checked")) {
                        			swal("Atenção", "Por favor confirme a alteração do ano do documento", "warning");
                        			return;
                    			}
                			}
            				
            				swal({
					        	title: "Aguarde...",
					        	showConfirmButton: false,
					        	allowOutsideClick: false,
					        	onOpen: function() {
					        		swal.showLoading();
						        	$.get("./core/actions/boxExists.php", {box:$("#doc_box").val()}, function(res) {
							        	if(res.ok) {// Caixa existe
								        	if(fileOK) {
								        		var _data = new FormData();
								        		_data.append(0, $("#doc_file")[0].files[0]);
								        		_data.append('doc_box', $("#doc_box").val());
								        		registerDocumentWithFile(_data);
								        	} else {
								        		registerDocument();
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

    	$("#form-register").submit(formRegisterSubmit);

    	$("#doc_number, #doc_letter, #doc_volume").change(function() {
        	$.get("core/actions/registrationAllowed.php", {
            	client: <?= $user->getClient() ?>,
            	box: $("#doc_box").val(),
            	type: $("#doc_type").val(),
            	year: $("#doc_year").val(),
            	number: $("#doc_number").val(),
            	letter: $("#doc_letter").val(),
            	volume: $("#doc_volume").val()
        		}, function(data) {
            		if(data.ok) {
            			$("#doc_number, #doc_letter").removeClass("is-invalid");
                		$("#doc_number, #doc_letter").addClass("is-valid");
            		} else {
                		$("#doc_number, #doc_letter").removeClass("is-valid");
            			$("#doc_number, #doc_letter").addClass("is-invalid");
                	}
            	}, 'json');
    	});

    	$("#doc_year").change(function() {
        	$("#doc_date").attr({
    	       "max" : $("#doc_year").val() + '-12-31',
    	       "min" : $("#doc_year").val() + '-01-01'
    	    });
    	});

    	$("#sealed").change(function() {
        	if($("#doc_box").val() != "") {
            	var _text = $('#sealed').is(":checked") ? "Deseja realmente selar a caixa?" : "Deseja realmente remover selo da caixa?";
            	swal({
                	title: "Selar caixa",
                	text: _text,
                	type:'question',
                	showCancelButton: true,
                	cancelButtonText: 'Não',
                	confirmButtonText: 'Sim'
            	}).then((result) => {
                	if(result.value) {
                		$.get("./core/actions/sealBox.php", {box_number : $("#doc_box").val(), sealed : $('#sealed').is(":checked") ? "on" : ""}, function(res) {
                            if(res.ok) {
                            	swal("Alteração realizada com sucesso", "", "info");
                            } else {
                            	swal(res.message, "", res.type);
                            }
                        }, 'json').fail(function() {
                        	swal("Erro", "Ocorreu um erro ao realizar a operação. Verifique sua conexão com a internet e tente novamente mais tarde.", "error");
                        });
                	}
                });
        	}
    	});

    	$("#doc_box").change(function() {
    		if($("#doc_box").val() != "") {
        		// Pega caixa que o usuário estava fazendo e verifica se está fechada
        		$.get('core/actions/isMyLastBoxSealed.php', function(box) {
            		if(box.sealed == false) {
                		swal({
                    		title: "Atenção",
                    		type: 'question',
                    		html: "<p>Sua última caixa cadastrada não foi selada!</p><p>Deseja selar a caixa " + box.number + "?</p>",
                    		showCancelButton: true,
                    		cancelButtonText: 'Não',
                    		confirmButtonText: 'Sim'
                    	}).then((result) => {
                        	if(result.value) {
                        		// Sela a caixa que estava cadastrando
                        		$.get("./core/actions/sealBox.php", {box_number : box.number, sealed : "on"}, function(res) {
                                    if(res.ok) {
                                    	swal("Alteração realizada com sucesso", "A caixa foi selada!", "info");
                                    } else {
                                    	swal(res.message, "", res.type);
                                    }
                                }, 'json').fail(function() {
                                	swal("Erro", "Ocorreu um erro ao realizar a operação. Verifique sua conexão com a internet e tente novamente mais tarde.", "error");
                                });
                        	}
                        });
            		} else {
            		}
            	}, 'json').fail(function() {
            		swal("Erro", "Ocorreu um erro ao realizar a operação. Verifique sua conexão com a internet e tente novamente mais tarde.", "error");
                });

            	$.get('core/actions/isBoxSealed.php', {box_number: $("#doc_box").val()}, function(data) {
                	if(data.ok) {
                    	$("#sealed").prop('checked', true);
                	} else {
                		$("#sealed").prop('checked', false);
                	}
            	}, 'json');
        	}
        });
    });
    function registerDocument() {
		// Success so call function to process the form
    	$.post('./core/actions/registerDocument.php', $("#form-register").serialize(), function(data) {
        	if(data.ok) {
	        	var doc_box = $("#doc_box").val();
        		var doc_year = $("#doc_year").val();
        		var doc_type = $("#doc_type").val();
        		var doc_number = $("#doc_number").val();
        		var box_sealed = $("#sealed").is(':checked');

        		$("#form-register")[0].reset();

        		$("#doc_box").val(doc_box);
        		$("#doc_year").val(doc_year);
        		$("#doc_type").val(doc_type);
        		$("#doc_token").val('');
        		$("#sealed").prop("checked", box_sealed);

        		swal({
        			title: "Cadastro realizado com sucesso!",
					html : (doc_number != "" ? ("Nº documento: " + doc_number) : "") + 
					"<br>Caixa: " + doc_box + "<br>Ano: " + doc_year,
	        		type : data.type
	        	}).then(() => {
		        	setTimeout(() => {
		        		$("#doc_number").focus();
		        		$("#doc_year").val(window.localStorage.getItem('box_' + doc_box)).trigger('change');
					}, 450);
				});
			} else {
				swal(data.error, "", data.type);
			}
    	}, 'json').fail(function() {
			swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
		});
	}

	function registerDocumentWithFile(_data) {
		$.ajax({
        	url: './core/actions/registerDocument.php?&files',
        	type: 'POST',
	        data: _data,
	        cache: false,
	        dataType: 'json',
	        processData: false, // Don't process the files
	        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
	        success: function(data, textStatus, jqXHR) {
		        if(data.ok == true) {
			        $("#doc_token").val(data.token);
       				registerDocument();
			    } else {
	                // Handle errors here
	            	swal("", data.error, "warning");
				}
	        }, error: function(jqXHR, textStatus, errorThrown) {
	            // Handle errors here
	            swal("", "Erro ao realizar upload dos arquivos", "error");
	        }
        });
	}

	function changeSealBox(obj) {
		$("#sealed").prop('checked', $(obj).is(":checked"));
	}
    </script>
    <?php include_once ('common_scripts.php'); ?>
</body>
</html>