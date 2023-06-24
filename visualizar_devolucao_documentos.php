<?php
include_once (dirname(__FILE__) . "/core/utils/Utils.php");
include_once (dirname(__FILE__) . "/core/utils/Input.php");
include_once (dirname(__FILE__) . "/core/model/Request.php");
include_once (dirname(__FILE__) . "/core/model/RequestType.php");
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/UserController.php");
include_once (dirname(__FILE__) . "/core/control/RequestController.php");
include_once (dirname(__FILE__) . "/core/control/DevolutionController.php");

use Docbox\control\DevolutionController;
use function Docbox\control\getUserLogged;
use Docbox\model\AbstractDocumentFormat;
use Docbox\model\DbConnection;
use Docbox\model\RequestType;
use Docbox\utils\Input;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$dev_id = Input::getInt('dev');
$devolutionController = new DevolutionController($db);

$devolution = $devolutionController->getDevolutionById($dev_id);

if($devolution == NULL)
{
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
                        <h3 class="text-themecolor mb-0 mt-0">Devoluções</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Devoluções</a></li>
                            <li class="breadcrumb-item active">Visualizar Devolução</li>
                        </ol>
                    </div>
                    <div class="col-md-6 col-4 align-self-center">
                    	<?php if(!empty($devolution->getFile())) {
                    		if(file_exists(dirname(__FILE__) . "/devolution_files/" . $devolution->getFile())) {
                    		?>
                        <a href='devolution_files/<?= $devolution->getFile() ?>' target='_blank' class="btn float-right hidden-sm-down btn-success ml-1">
		                        	<i class="fa fa-download"></i> Comprovante</a>
		                <?php }
                    	} ?>
                    	<?php if($user->isAdmin()) { ?>
                        <a href="imprimir_devolucao.php?dev=<?= $dev_id ?>" target='_blank' class="btn float-right hidden-sm-down btn-secondary"><i class="fa fa-print"></i> Imprimir</a>
                        <?php } ?>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                 <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Devolução</h4>
                                <h6 class="card-subtitle">Detalhes da devolução</h6>
                                <form id='form-devolution' method='' action='#'>
                                	<div class='row'>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="dev_number">N&ordm; da devolução</label>
			                                        <input id='dev_number' name='req_number' type="number" class="form-control inputDisabled" placeholder="0" min='1' value='<?= $devolution->getNumber() ?>' disabled="disabled">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="dev_when">Data/Hora</label>
			                                        <input id='dev_when' name='dev_when' type="text" class="form-control inputDisabled" value='<?= $devolution->getDatetime()->format("d/m/Y à\s H:i:s") ?>' disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="dev_user">Solicitante</label>
		                                        <input id='dev_user' name='dev_user' type="text" class="form-control inputDisabled" value="<?= $devolution->getUser()->getName() ?>" disabled="disabled">
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="dev_status">Situação</label>
		                                        <input id='dev_status' name='dev_status' type="text" class="form-control inputDisabled" value="<?= empty($devolution->getFile()) ? "Em aberto" : "Concluída" ?>" disabled="disabled">
		                                    </div>
                                		</div>
                                		<?php 
		                                if($user->isAdmin()) {
		                                	if(empty($devolution->getFile()) || !file_exists(dirname(__FILE__) . "/devolution_files/" . $devolution->getFile())) { ?>
			                                	<div id='rowFileInput' class='col-md-6'>
				                                    <div class="form-group">
				                                    	<div class='controls'>
						                                    <label>Comprovante digitalizado</label>
						                                    <input id='dev_file' name="dev_file" type="file" class="form-control" accept='.pdf,image/*' required data-validation-required-message="Selecione o arquivo comprovante">
				                                    	</div>
					                                </div>
				                                </div>
		                                <?php
												}
											}
										?>
                                	</div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Documentos</h4>
                                <h6 class="card-subtitle">Esta tabela exibe os documentos da devolução</h6>
                                <div class="table-responsive m-t-40">
                                    <table id="data-table-basic" class="table table-bordered table-striped">
                                    	<thead>
		                                <tr>
		                                	<th>Pedido</th>
		                                	<th>Caixa</th>
		                                    <th>Formato</th>
		                                    <th>Tipo</th>
		                                    <th>Número</th>
		                                    <th>Ano</th>
		                                    <th>Letra</th>
		                                    <th>Volume</th>
		                                    <th>Título</th>
		                                </tr>
		                                </thead>
		                                <tfoot>
		                                <tr>
		                                    <th>Pedido</th>
		                                	<th>Caixa</th>
		                                    <th>Formato</th>
		                                    <th>Tipo</th>
		                                    <th>Número</th>
		                                    <th>Ano</th>
		                                    <th>Letra</th>
		                                    <th>Volume</th>
		                                    <th>Título</th>
		                                </tr>
		                                </tfoot>
		                                <tbody>
		                                <?php
											if($devolution->getReqType() == RequestType::DOCUMENT) {
												$i = 0;
												$docs = $devolutionController->getDocumentsFromDevolution($devolution->getId());

												foreach($docs as $doc) {
													$document = $doc->getRequestDocument()->getDocument();
													$i++;
													?>
													<tr class='<?= ($i % 2 == 0)?"even":"odd"; ?>'>
														<td><?= $doc->getRequestNumber() ?></td>
														<td><?= $document->getBox()->getNumber() ?></td>
														<td><?= $document->getFormat() == AbstractDocumentFormat::BOOK ? "Livro" : "Documento" ?></td>
								    					<td><?= $document->getType()->getDescription() ?></td>
								    					<td><?= $document->getFormat() == AbstractDocumentFormat::BOOK ? ($document->getNumFrom() . "-" . $document->getNumTo()) : $document->getNumber() ?></td>
								    					<td><?= $document->getYear() ?></td>
								    					<td><?= $document->getFormat() == AbstractDocumentFormat::BOOK ? "" : $document->getLetter() ?></td>
								    					<td><?= $document->getVolume() ?></td>
								    					<td><?= $document->getCompany() ?></td>
													</tr>
												<?php } ?>
											<?php } ?>
		                                </tbody>
                                    </table>
                                </div>
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
    <!--Custom JavaScript -->
    <script src="js/session.min.js"></script>
    <script src="js/app.min.js"></script>
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
    <!-- Sweet-Alert  -->
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="assets/plugins/sweetalert/jquery.sweet-alert.custom.js"></script>
    <!-- This is data table -->
    <script src="assets/plugins/datatables/datatables.min.js"></script>

    <!-- end - This is for export functionality only -->
    <script>
        $(document).ready(function() {
        	var tbRequests = $('#data-table-basic').DataTable({
            	"serverSide": false,
            	"processing": true,
            	"aaSorting": [4, "asc"],
            	"language" : {
            	    "sEmptyTable": "Nenhum documento",
            	    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ documentos",
            	    "sInfoEmpty": "Mostrando 0 até 0 de 0 documentos",
            	    "sInfoFiltered": "(Filtrados de _MAX_ documentos)",
            	    "sInfoPostFix": "",
            	    "sInfoThousands": ".",
            	    "sLengthMenu": "_MENU_ resultados por página",
            	    "sLoadingRecords": "Carregando...",
            	    "sProcessing": "Processando...",
            	    "sZeroRecords": "Nenhum documento",
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
            	"aoColumnDefs" : [
                	{
	                	"aTargets": [0],
	            	}, {
                    	'aTargets':[4]// , 'className': 'dt-body-center',
                	}
                ]
            });
        	$("#dev_file").change(function() {
                var returnData = <?= $dev_id ?>;
                var btFileInput = $(this);
                if($(this).val() != "" && $(this)[0].files.length == 1) {
    	        	if(belowMaxUploadSize($(this).prop('id'))) {
    	        		$(this).parents(".form-group").removeClass("error");
    	        		swal({
                            type:'question',
                            title:'Finalizar devolução',
                            text: 'Deseja realmente incluir o arquivo e finalizar a devolução dos documentos?',
                            showCancelButton: true,
                            cancelButtonText: 'Não',
                            confirmButtonText:'Sim'
                        }).then((result) => {
                            if(result.value) {
    	                        swal.showLoading();
    	                        var _data = new FormData();
    	    	        		_data.append(0, btFileInput[0].files[0]);
    	                        $.ajax({
    	    	        			url: './core/actions/finishRequestReturn.php?&files',
    					        	type: 'POST',
    						        data: _data,
    						        cache: false,
    						        dataType: 'json',
    						        processData: false, // Don't process the files
    						        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
    						        success: function(data, textStatus, jqXHR) {
    							        if(data.ok) {
    							        	$.post('./core/actions/finishRequestReturn.php', {ret: returnData, token : data.token}, function(data) {
    					                        if(data.ok) {
    					                        	swal("Devolução concluída com sucesso!", "", "success").then(function() {
        					                        	window.location.reload();
        					                        });
    					                        } else {
    					                        	swal("Não foi possível realizar a ação", "", "error");
    					                        }
    				                        }, 'json').fail(function() {
    				                        	swal("Erro ao realizar ação", "Por favor verifique sua conexão com a internet e tente novamente mais tarde.", "error");
    				                        });
    								    } else {
    								    	swal("", "Erro ao realizar upload dos arquivos", "error");
    									}
    							    }, error: function(jqXHR, textStatus, errorThrown) {
    						            swal("", "Erro ao realizar upload dos arquivos", "error");
    						        }
    	        	        	});
                            }
                        });
    	        	} else {
    		        	$(this).parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>O arquivo deve ser menor que " + MAX_UPLOAD_SIZE + "Mb</li><li>");
    		        	$(this).parents(".form-group").addClass("error").removeClass("validate");
    			    }
                } else {
                	$(this).parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>Selecione o comprovante de entrega.</li><li>");
                	$(this).parents(".form-group").addClass("error").removeClass("validate");
                }
            });
        	$("#menuDevolutions").addClass("active").css("background-color", "white");
        });
    </script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    <?php include_once ('common_scripts.php'); ?>
</body>
</html>