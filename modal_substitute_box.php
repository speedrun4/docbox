<div class="modal fade" id="modalSubstituteBox" tabindex="-1" role="dialog" aria-labelledby="modalSubstituteBox">
	<div class="modal-dialog modal-lg" role="document">
		<form id="form-substitute-box" method="post" action="#">
			<input type='hidden' name='box' value='<?php echo $box->getId(); ?>'>
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="modalSubstituteBox">Transferir Documentos</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p>A caixa que deseja excluir não está vazia. Informe a caixa para qual os documentos serão transferidos.</p>
					<div class="row">
						<div class='col-md-12'>
			            	<div class="form-group">
			                	<div class='controls'>
					                <label for="new_box_number">N&ordm; da caixa</label>
									<input id='new_box_number' name='new_box_number' type="number" class="form-control" placeholder="" min='1'>
								</div>
							</div>
	                    </div>
					</div>
					<div class="custom-control custom-checkbox mr-sm-2 mb-3">
                        <input type="checkbox" class="custom-control-input" id="del_content" name="del_content" value="on">
                        <label class="custom-control-label text-danger" for="del_content">Excluir os documentos da caixa</label>
                    </div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-danger"> <i class='fa fa-trash'></i> Confirmar</button>
				</div>
			</div>
		</form>
	</div>
</div>