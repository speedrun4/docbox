<script src="assets/plugins/react16/react.production.min.js"></script>
<script src="assets/plugins/react16/react-dom.production.min.js"></script>
<?php if (isset($user) && $user->isAdmin()) { ?>
	<script type="text/javascript" src="js/components/request_notification/RequestNotification.js"></script>
	<script type="text/javascript" src="js/components/request_notification/RequestNotificationItem.js"></script>
	<script type="text/javascript">
		ReactDOM.render(React.createElement(RequestNotification, {'request':""}), document.getElementById('request_notifications'));
	</script>
<?php } ?>