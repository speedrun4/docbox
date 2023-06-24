<div class="modal fade" id="modalChooseLocation" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
	<div class="modal-dialog modal-lg" role="document">
		<form id="form-register-box" method="post" action="#">
			<input type="hidden" name="lab_position" id="lab_position" value="1">
			<input type="hidden" name="lab_box" value="<?php if(isset($box)) echo $box->getId(); ?>">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1">Imprimir etiqueta de caixa</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-12">
                            <div class="pageImage">
                            	<div data-value="1" class="pageLabel pageLabel--selected"></div>
                            	<div data-value="2" class="pageLabel"></div>
                            	<div data-value="3" class="pageLabel"></div>
                            	<div data-value="4" class="pageLabel"></div>
                            </div>
                        </div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</form>
	</div>
</div>