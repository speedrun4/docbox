$(document).ready(function () {
	initSessionMonitor();

	if($('.typeahead')[0]) {
	    var bestPictures = new Bloodhound({
		  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
		  queryTokenizer: Bloodhound.tokenizers.whitespace,
		  remote: {
		    url: './core/actions/queryTitles.php?title=%QUERY',
		    wildcard: '%QUERY'
		  }
		});

	    var options = {
	    	highlight: true,
	    };

	    $('.typeahead').typeahead(options, {
		  name: 'best-pictures',
		  display: 'value',
		  source: bestPictures,
		  limit: 10
		});
	}
});

function blockFormEnter(form) {
	$(document).ready(function() {
        $('input').keypress(function(e) {
            var code = null;
            code = (e.keyCode ? e.keyCode : e.which);
            return (code === 13) ? false : true;
        });
 
        $('input.form-control').keydown(function(e) {
            // Obter o próximo índice do elemento de entrada de texto
            var next_idx = $('input.form-control:not(.tt-hint)').index(this) + 1;
 
            // Obter o número de elemento de entrada de texto em um documento html
            var tot_idx = $('body').find('input.form-control:not(.tt-hint)').length;
 
            // Entra na tecla no código ASCII
            if (e.keyCode === 13) {
                if (tot_idx === next_idx) {
                    // Vá para o primeiro elemento de texto
                    $('input.form-control:not(.tt-hint)').eq(0).focus();
                } else {
                	// console.log($('input.form-control').eq(next_idx));
                    // Vá para o elemento de entrada de texto seguinte
                    $('input.form-control:not(.tt-hint), .btn').eq(next_idx).focus();
                }
            }
        });
    });
}

function showErrorBlock(elem, message) {
	elem.parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>" + message + "</li><li>");
	elem.parents(".form-group").addClass("error").removeClass("validate");
}

function isPasswordValid(elem) {
	if(elem == undefined) return false;
	/* 6 to 15 characters which contain only characters, numeric digits,
	 * underscore and first character must be a letter */
	var passw =  /^[A-Za-z0-9]\w{5,14}$/;

	if(!elem.val().match(passw)) {
		showErrorBlock(elem, "Senha deve possuir no mínimo 6 caracteres e deve possuir letras e números");
		return false;
	}

	return true;
}

function changeClient(elem) {
	$.post("./core/actions/changeClient.php", {client:$(elem).data().client}, function(res) {
		if(res.ok) {
			location.reload();
		} else {
			swal("Erro ao alterar cliente", "", "error");
		}
	}, 'json').fail(function() {
		swal("Erro ao alterar cliente", "", "error");
	});
}

var MAX_UPLOAD_SIZE = 45.00;

function belowMaxUploadSize(input_id) {
	var megabytes = parseFloat((($('#' + input_id)[0].files[0].size / 1024) / 1024).toFixed(4)); // MB
	return megabytes <= MAX_UPLOAD_SIZE;
}
