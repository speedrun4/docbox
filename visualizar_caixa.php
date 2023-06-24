<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/BoxController.php");
include_once (dirname(__FILE__) . "/core/utils/Utils.php");
include_once (dirname(__FILE__) . "/core/model/User.php");
include_once (dirname(__FILE__) . "/core/config/Configuration.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$boxController = new BoxController($db);

$box_id = getReqParam("box", "int", "get");
$box = $boxController->getBoxById($box_id);

if($box == NULL) {
	header("Location: login.php");
}

if(!$user->isAdmin() && $box->getClient() != $user->getClient()) {
	header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <?php include('head.php'); ?>
    <link href="assets/plugins/datatables/media/css/dataTables.foundation.css" rel="stylesheet">
</head>

<body class="fix-header card-no-border">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /></svg>
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
                        <h3 class="text-themecolor m-b-0 m-t-0">Caixas</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Caixas</a></li>
                            <li class="breadcrumb-item"><a href="listar_caixas.php">Listar Caixas</a></li>
                            <li class="breadcrumb-item active">Visualizar caixa</li>
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
	                            <?php
	                            $colSize = "col-md-4";//$user->isAdmin() ? "col-md-2" : "col-md-4";
		                        ?>
                                <h4 class="card-title">Caixa</h4>
                                <h6 class="card-subtitle"> Informações da caixa </h6>
                                <form id='form-register' class="mt-4" method='post' action='#' novalidate>
	                                <input id="box_id" name="box_id" type="hidden" value='<?= $box_id ?>'>
                                	<div class='row'>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="box_number">Nº da caixa</label>
			                                        <input id='box_number' name='box_number' type="number" class="form-control" placeholder="0" min='1' value="<?php echo $box->getNumber(); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		 <?php if($user->isAdmin() && isset($palmeirasNaoTemMundial)) { ?>
                                		 <div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="box_corridor">Corredor</label>
			                                        <input id='box_corridor' name='box_corridor' type="text" class="form-control" value="<?php 
			                                    echo chr($box->getCorridor() + 64);
			                                    ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="box_tower">Torre</label>
			                                        <input id='box_tower' name='box_tower' type="number" class="form-control" value="<?php echo $box->getTower(); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="box_floor">Andar</label>
			                                        <input id='box_floor' name='box_floor' type="number" class="form-control" value="<?php echo $box->getFloor(); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		 <?php } ?>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="box_department">Departamento</label>
			                                        <input id='box_department' name='box_department' type="text" class="form-control" value="<?php echo $box->getDepartment()->getName(); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                        <label for="doc_number">Situação</label>
		                                        <input type="text" class="form-control" value="<?php
			                                    if($box->getRequest() == NULL || $box->getRequest()->getStatus() == NULL || $box->getRequest()->getStatus() == 2 || $box->getRequest()->getStatus() == 5) {
			                                        echo COMPANY_NAME;
			                                    } else {
			                                        echo "Em pedido";
			                                    }
			                                    ?>" disabled>
		                                    </div>
                                		</div>
                                	</div>
                                	<?php if($user->isAdmin()) { ?>
                                	<div class='row'>
                                		<div class='col-md-2 offset-md-10'>
                                			 <div class="form-group">
                                                <div class="custom-control custom-checkbox mr-sm-2">
                                                    <input type="checkbox" class="custom-control-input" id="chkBoxSealed" name='sealed' value="on" <?php if($box->isSealed()) echo "checked";?>>
                                                    <label class="custom-control-label" for="chkBoxSealed">Caixa selada</label>
                                                </div>
                                            </div>
                                		</div>
                                	</div>
                                	<button id='btDeleteBox' type='button' class='btn btn-danger btn-icon'><i class='fa fa-trash'></i> Excluir</button>
                                	<?php } ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
				<?php if($hasDocs = $boxController->hasDocs($box)) { ?>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<h4 class="card-title">Documentos</h4>
								<h6 class="card-subtitle">Lista dos documentos armazenados na caixa</h6>
								<button id="btProcessUploads" class='btn btn-success btn-icon'><i class='fa fa-upload'> </i> Processar uploads</button>
								<input id="input-files" class='input-files' type='file' multiple/>
								<div class="table-responsive m-t-40">
									<table id="data-table-basic" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>Ações</th>
												<th>Caixa</th>
												<th>Tipo</th>
												<th>Número</th>
												<th>Ano</th>
												<th>Letra</th>
												<th>Volume</th>
												<th>Título</th>
												<th>Data</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>#</th>
												<th>Ações</th>
												<th>Caixa</th>
												<th>Tipo</th>
												<th>Número</th>
												<th>Ano</th>
												<th>Letra</th>
												<th>Volume</th>
												<th>Título</th>
												<th>Data</th>
											</tr>
										</tfoot>
										<tbody>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php } else if($hasBooks = $boxController->hasBooks($box)) { ?>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<h4 class="card-title">Livros</h4>
								<h6 class="card-subtitle">Lista dos livros armazenados na caixa</h6>
								<div class="table-responsive m-t-40">
									<table id="data-table-basic" class="table table-bordered table-striped">
										<thead>
                                            <tr>
                                                <th>#</th>
			                                    <th>Ações</th>
			                                    <th>Caixa</th>
			                                    <th>Tipo</th>
			                                    <th>Ano</th>
			                                    <th>N&ordm; inicial</th>
			                                    <th>N&ordm; final</th>
			                                    <th>Volume</th>
			                                    <th>Local</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
			                                <tr>
			                                    <th>#</th>
			                                    <th>Ações</th>
			                                    <th>Caixa</th>
			                                    <th>Tipo</th>
			                                    <th>Ano</th>
			                                    <th>N&ordm; inicial</th>
			                                    <th>N&ordm; final</th>
			                                    <th>Volume</th>
			                                    <th>Local</th>
			                                </tr>
		                                </tfoot>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php } else { ?>
				<p>Caixa vazia...</p>
				<?php } ?>
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
    <?php
	    include("modal_substitute_box.php");
	    include("modal_upload_files.php");
    ?>
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
    <!-- This is data table -->
    <script src="assets/plugins/datatables/datatables.min.js"></script>
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
    <script type="text/javascript">
    ! function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation()
    }(window, document, jQuery);

    var tbDocuments = null; 

    
    $(function() {
        <?php if($hasDocs) { ?>
    	tbDocuments = $('#data-table-basic').DataTable({
        	"serverSide": true,
        	"processing": true,
			"searching":false,
			"pageLength": 100,
        	"aaSorting": [4, "asc"],
        	"language" : {
        	    "sEmptyTable": "Nenhum registro encontrado",
        	    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
        	    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
        	    "sInfoFiltered": "",
        	    "sInfoPostFix": "",
        	    "sInfoThousands": ".",
        	    "sLengthMenu": "_MENU_ resultados por página",
        	    "sLoadingRecords": "Carregando...",
        	    "sProcessing": "Processando...",
        	    "sZeroRecords": "Nenhum registro encontrado",
        	    "sSearch": "Pesquisar",
        	    "oPaginate": {
        	        "sNext": "Próximo",
        	        "sPrevious": "Anterior",
        	        "sFirst": "Primeiro",
        	        "sLast": "Último"
        	    }, "oAria": {
        	        "sSortAscending": ": Ordenar colunas de forma ascendente",
        	        "sSortDescending": ": Ordenar colunas de forma descendente"
        	    }
        	},
        	"sAjaxSource" : "./core/data-tables/TableDocuments.php",
        	"sServerMethod" : "POST",
        	"fnServerParams" : function(aoData) {
				aoData.push({"name" : "doc_box", "value" : <?php echo $box->getId(); ?>});
        	}, "aoColumnDefs" : [{
					"aTargets": [0],
					"visible" : false
                }, {
            		"aTargets": [1],
            		orderable: false,
                    width: 130,
                    "mRender": function(d,t,f) {
                        var content = "";
                        <?php if($user->isAdmin()) { ?>
                        	content += "<a target='blank' href='editar_documento.php?doc=" + f[0] + "' class='btn btn-circle btn-warning'><i class='fa fa-edit'></i></a>";
                        <?php } ?>
                        if(f[12] != "" && f[12] != null) {
                           	content += "<a target='blank' href='" + f[12] + "' class='btn btn-circle btn-primary'><i class='fa fa-file'></i></a>";
                        } else {
                        	// content += "<input class='input-files' data-doc='" + f[0] + "' type='file'/><div class='alert alert-danger m-2 d-none' role='alert'></div>";
                        }
                       	return content;
                    }
                }, {
                	"aTargets": [4],
                    "mRender":function(d,t,f) {
                        return parseInt(d) > 0 ? d : "";
                    }
                }
            ]
        });
        <?php } else if($hasBooks) { ?>
        tbDocuments = $('#data-table-basic').DataTable({
        	"serverSide": true,
        	"processing": true,
        	"aaSorting": [5, "asc"],
        	"language" : {
        	    "sEmptyTable": "Nenhum documento encontrado",
        	    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ documentos",
        	    "sInfoEmpty": "Mostrando 0 até 0 de 0 documentos",
        	    "sInfoFiltered": "(Filtrados de _MAX_ documentos)",
        	    "sInfoPostFix": "",
        	    "sInfoThousands": ".",
        	    "sLengthMenu": "_MENU_ resultados por página",
        	    "sLoadingRecords": "Carregando...",
        	    "sProcessing": "Processando...",
        	    "sZeroRecords": "Nenhum documento encontrado",
        	    "sSearch": "Pesquisar",
        	    "oPaginate": {
        	        "sNext": "Próximo",
        	        "sPrevious": "Anterior",
        	        "sFirst": "Primeiro",
        	        "sLast": "Último"
        	    },
        	    "oAria": {
        	        "sSortAscending": ": Ordenar colunas de forma ascendente",
        	        "sSortDescending": ": Ordenar colunas de forma descendente"
        	    }
        	},
        	"sAjaxSource": "./core/data-tables/TableBooks.php",
        	"sServerMethod" : "POST",
        	"fnServerParams" : function(aoData) {
        		aoData.push({"name" : "doc_box", "value" : <?php echo $box->getId(); ?>});
        	}, 
        	"aoColumnDefs" : [{
                	"aTargets": [0],
                	"visible":false
            	}, {
            		"aTargets": [1],
            		orderable: false,
                    width: 120,
                    "mRender": function(d,t,f) {
                        var content = "";
                        <?php if($user->isAdmin()) { ?>
                            // Botão de edição
                            content += "<a href='editar_livro.php?book=" + f[0] + "' class='btn btn-circle btn-success'><i class='fa fa-edit'></i></a>";
                        <?php } ?>
                    	return content;
                    }
                }, {
                    "aTargets": [2],
                    width: 72,
                    "mRender": function(d,t,f) {
                        return "<a href='visualizar_caixa.php?box=" + f[1] + "'>" + d + "</a>";
                    }
                }, {
                	"aTargets" : [3],
                	//visible:false
                }, {
                	"aTargets": [8],
                	"mRender":function(d,t,f) {
                    	if(d > 0) {
                        	return "<a href='visualizar_pedido_caixas.php?r=" + d + "'>Em pedido</a>";
                        } else {
                            return "<?= COMPANY_NAME ?>";
                        }
                    }
                }
            ]
        });
        <?php } ?>

        $("#btDeleteBox").click(function() {
            // Verifica se a caixa está vazia
            if(<?php if($boxController->isBoxEmpty($box)) {
                echo 'true';
            } else { echo 'false'; } ?>) {
                swal({
                    type: 'question',
                    title: "Deseja realmente excluir a caixa?",
                    cancelButtonText: 'Não',
                    confirmButtonText: 'Sim',
                    showCancelButton: true,
                    confirmButtonColor: 'crimson'
                }).then((result) => {
                    if(result.value) {
                        $.post("./core/actions/deleteBox.php", {box:<?php echo $box->getId(); ?>}, function(res) {
                            if(res.ok == true) {
                                swal("Caixa excluída com sucesso!", "", "success").then(()=> { window.location.href = "listar_caixas.php" });
                            } else {
                                swal("Atenção", "Não foi possível excluir a caixa", "warning");
                            }
                        }, "json").fail(function() {
                            swal("Erro", "Ocorreu um erro ao realizar a operação. Verifique sua conexão com a internet e tente novamente mais tarde.", "error");
                        });
                    }
                });
            } else {
                // Informe o número da caixa que recebe os arquivos
                $("#form-substitute-box").trigger('reset');
                $("#modalSubstituteBox").modal('show');
            }
        });

        $("#chkBoxSealed").change(function() {
            $.get("./core/actions/sealBox.php", $("#form-register").serialize(), function(res) {
                if(res.ok) {
                	swal("Alteração realizada com sucesso", "", "info");
                } else {
                	swal(res.message, "", res.type);
                }
            }, 'json').fail(function() {
            	swal("Erro", "Ocorreu um erro ao realizar a operação. Verifique sua conexão com a internet e tente novamente mais tarde.", "error");
            });
        });

        $("#form-substitute-box").submit(function(e) {
			$("#modalSubstituteBox").modal('hide');
			swal({
				title: "Aguarde...",
			   	showConfirmButton: false,
			   	allowOutsideClick: false,
			   	onOpen: function() {
    				swal.showLoading();
    				$.post('./core/actions/deleteBox.php', $("#form-substitute-box").serialize(), function(res) {
    			  		if(res.ok) {
    			       		swal("Exclusão realizada com sucesso!", "", "success").then(()=> { window.location.href = "listar_caixas.php"; });
    			       	} else {
    			       		swal("Erro ao realizar a operação", res.message, res.type);
    			        }
    			    }, 'json').fail(function(){
			      		swal("Erro ao realizar a operação", "Por favor tente novamente mais tarde. Se o problema persistir entre em contato com o suporte do programa.", "error");
    				});
    			}
			});
			e.preventDefault();
		});

        $("#btProcessUploads").click(function() {
        	var files = Array.from($("#input-files")[0].files);
            if(files.length > 0) {
            	$("#modalProcessUpload").modal('show');
  				$("#modalProcessUpload__spinner").show();
   	            $("#divProcessing").text("");
            	uploadFiles(files);
            } else {
            	swal("Nenhum arquivo selecionado", "", "warning");
            }
        });
    });

    function onUploadFilesEnd(totalFiles, uploadErrors) {
		$("#modalProcessUpload__spinner").hide();

		if(uploadErrors == totalFiles) {
			swal("Erro", "Falha no upload dos arquivos. Verifique as mensagens na tabela.", "error");
		} else if(uploadErrors > 0) {
       		swal("Processo concluído",
               		"Falha ao realizar o upload de " + uploadErrors + " arquivo" + (uploadErrors > 1 ? "s" : "") + ". Verifique as mensagens na tabela.",
               		"warning").then((result) => {
					tbDocuments.ajax.reload();
           		});
		} else {
			$("#modalProcessUpload").modal('hide');
			swal("Processo concluído", "Todos os arquivos enviados com sucesso", "success").then((result) => {
				if(result.value) {
					tbDocuments.ajax.reload();
				}
			});
		}
	}

	var worker = null;

	function uploadFiles(_files) {
		var totalFiles = _files.length;
        var uploadErrors = 0;

		worker = new Worker("js/upload_worker.js");
        worker.onmessage = function receivedWorkerMessage(event) {
            // Get the prime number list.
            var data = event.data;

            if (data.messageType == "success") {
            	$("#divProcessing").prepend("<div class='alert alert-success'>" + data.file + " : " + data.message + "</div>");
            } else if (data.messageType == "error") {
            	uploadErrors++;
            	$("#divProcessing").prepend("<div class='alert alert-danger'>" + data.file + " : " + data.message + "</div>");
            } else if(data.messageType == "End") {
                $("#modalProcessUpload__spinner").hide();
                onUploadFilesEnd(totalFiles, uploadErrors);
                setTimeout(function() {
                	terminateWorker();
				}, 1000);
            }
        };
        worker.postMessage({
            box : <?= $box_id ?>,
            files : _files,
            'MAX_UPLOAD_SIZE' : MAX_UPLOAD_SIZE
        });
	}

	function terminateWorker() {
        worker.terminate();
        worker = undefined;
    }
    </script>
</body>

</html>