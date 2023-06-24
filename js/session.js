// How frequently to check for session expiration in milliseconds
var sess_pollInterval = 10 * 60000;// A cada x minutos
var sess_expirationMinutes = 3 * 60;// x horas
var sess_warningMinutes = sess_expirationMinutes - 2;//(2 * 60) - 2;// x horas - 2 min
var sess_intervalID, sess_lastActivity;

function initSessionMonitor() {
	sess_lastActivity = new Date();
	sessSetInterval();
	$(document).bind('keypress.session', function (ed, e) { sessKeyPressed(ed, e); });
}

function sessSetInterval() {
	sess_intervalID = setInterval('sessInterval()', sess_pollInterval);
}

function sessClearInterval() {
	clearInterval(sess_intervalID);
}

function sessKeyPressed(ed, e) {
	sess_lastActivity = new Date();
}

function sessPingServer() {
	$.get("core/actions/ping.php");
}

function sessLogOut() {
	window.location.href = "core/actions/doLogout.php";
}

function sessInterval() {
	var now = new Date();
	var diff = now - sess_lastActivity;
	var diffMins = (diff / 1000 / 60);
	
	if(diffMins >= sess_warningMinutes) {
		sessClearInterval();
		if(confirm('(' + now.toTimeString() + ')\nSua sessão está expirando em ' + (sess_expirationMinutes - sess_warningMinutes) + ' minutos. Clique em OK para continuar logado ou Cancelar para sair.')) {
			now = new Date();
			diff = now - sess_lastActivity;
			diffMins = (diff / 1000 / 60);

			if(diffMins > sess_expirationMinutes) {
				sessLogOut();
			} else {
				sessPingServer();
				sessSetInterval();
				sess_lastActivity = new Date();
			}
		} else {
			sessLogOut();
		}
	} else {
		// sessPingServer();
	}
}