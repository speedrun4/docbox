<div class="modal fade" id="modalProcessUpload" tabindex="-1" role="dialog" aria-labelledby="modalProcessUpload">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="modalSubstituteBox">Upload de Documentos</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div id="modalProcessUpload__spinner" class="mx-auto" style="text-align:center">
					<div class="spinner-border text-primary" role="status">
						<span class="sr-only">Enviando...</span>
					</div>
				</div>
				<div id="divProcessing" style="max-height: 400px; overflow-y: scroll;"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="showErrorWindow()">Janela de erros</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
			</div>
		</div>
	</div>
</div>
<script>
	function showErrorWindow() {
		var errorWindow = window.open("", "MsgWindow", "width=800,height=400");
		var html = "<head><link href='assets/plugins/bootstrap/css/bootstrap.min.css' rel='stylesheet'><link href='css/style.min.css' rel='stylesheet'></head>";
		html += "<div class='container'>";
		$("#divProcessing").each(function(index) {
			html += ($(this).html());
		});
		html += "</div>";
		errorWindow.document.write(html);
	}
</script>