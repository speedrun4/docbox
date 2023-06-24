<div class="modal fade" id="modal-update-type" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog" role="document">
		<form id="form-update-type" method="post" action="#" onsubmit="return updateType()">
			<input id="up_type_id" type="hidden" name="up_type_id" value="0">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1">Alterar Tipo de documento</h4>
					<button type="button" class="close" data-dismiss="modal"
						aria-label="Fechar">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class='col-md-12'>
							<div class="form-group">
								<div class='controls'>
									<label for="up_type_name">Descrição <span class="text-danger">*</span></label>
									<input id='up_type_name' name='up_type_name' type="text"
										class="form-control" placeholder="" required
										data-validation-required-message="Por favor preencha este campo"
										maxlength="45" autocomplete="off">
								</div>
							</div>
							<div class="form-group">
								<div class='controls'>
									<label for="up_type_preffix">Prefixo <span class="text-danger">*</span></label>
									<input id='up_type_preffix' name='up_type_preffix' type="text"
										class="form-control" placeholder="" required
										data-validation-required-message="Por favor preencha este campo"
										maxlength="3" minlength="3" autocomplete="off">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary btn-icon">
						<i class='fa fa-save'></i> Salvar</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
function updateType(e) {
	$.post("./core/actions/updateType.php", $("#form-update-type").serialize(), function(data) {
		if(data.ok) {
			tbTypes.ajax.reload();
			swal("Alteração realizada com sucesso!", "", data.type);
		} else {
			swal(data.error, "", data.type);
		}
		$("#modal-update-type").modal('hide');
	}, 'json').fail(function(xhr, status, error) {
	  	swal("Erro ao realizar pedido", "Por favor tente novamente mais tarde. Se o problema persistir entre em contato com o suporte do programa.", "error");
	});
	return false;
}
</script>