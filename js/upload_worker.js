function uploadFiles(box, files, MAX_UPLOAD_SIZE) {
	if (files.length > 0) {
		// Work on it
		const file = files.pop();
		var pattern = /[a-zA-Z]{3}\d{4,}([a-zA-Z]+)*_\d{4}(_VOL\d+)*.pdf/g;

		// Percorre os arquivos
		if (file.name.match(pattern)) {
			if (file.name.toLowerCase().endsWith("pdf")) {
				var fileSizeInMB = parseFloat(((file.size / 1024) / 1024).toFixed(4)); // MB
				if (fileSizeInMB > MAX_UPLOAD_SIZE) {
					postMessage({ messageType: "error", file: file.name, message: "O arquivo deve ser menor que " + MAX_UPLOAD_SIZE + "Mb" });
					return;
				}
			} else {
				postMessage({ messageType: "error", file: file.name, message: "O arquivo deve ser do tipo PDF" });
				return;
			}
			var formData = new FormData();
			formData.set('file', file);
			var request = new XMLHttpRequest();
			request.responseType = 'json';
			request.open("POST", '../core/actions/updateDocument.php?&files', false);
			request.onload = function() {
				var jsonResponse = request.response;
				// do something with jsonResponse
				if (jsonResponse != null && jsonResponse.ok == true) {
					var req = new XMLHttpRequest();
					req.responseType = 'json';
					req.open("POST", '../core/actions/updateDocumentFile.php', false);
					req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
					req.onload = function() {
						if (this.status != 200) {
							postMessage({ messageType: "error", file: file.name, message: "Erro de conexão com o servidor" });//"<div class='alert alert-danger'>Erro no upload do arquivo " + file.name + " : Erro de conexão com o servidor</div>");
						} else if(this.readyState == 4 && this.status==200) {
							var dataResponse = req.response;
							if (dataResponse != null) {
								if (dataResponse.ok) {
									postMessage({ messageType: "success", file: file.name, message: "Arquivo enviado com sucesso!" });
								} else {
									postMessage({ messageType: "error", file: file.name, message: dataResponse.error });
								}
							} else {
								postMessage({ messageType: "error", file: file.name, message: "Erro de conexão com o servidor" });
							}
						}
					};
					req.onerror = function() {
						postMessage({ messageType: "error", file: file.name, message: "Erro de conexão com o servidor" });
					}

					var params = new URLSearchParams();
					params.append('box', box);
					params.append('filename', file.name);
					params.append('doc_token', jsonResponse.token);

					req.send(params);
					setTimeout(function() {
						// Go to next
						uploadFiles(box, files, MAX_UPLOAD_SIZE);
					}, 100);
				} else { // Handle errors here
					postMessage({ messageType: "error", file: file.name, message: "Erro de conexão com o servidor" });
				}
			};
			request.onerror = function() {
				postMessage({ messageType: "error", file: file.name, message: "Erro de conexão com o servidor" });
			}
			request.send(formData);
		} else {
			postMessage({ messageType: "error", file: file.name, message: "Nome do arquivo não possui formato esperado" });
			setTimeout(function() {
				// Go to next
				uploadFiles(box, files, MAX_UPLOAD_SIZE);
			}, 100);
		}
	} else {
		// Now the search is finished. Send back the results.
		 postMessage({ messageType: "End" });
	}
}

onmessage = function(event) {
	// The object that the web page sent is stored in the event.data property.
	uploadFiles(event.data.box, event.data.files, event.data.MAX_UPLOAD_SIZE);
};