class RequestNotification extends React.Component {
	INTERVAL_IN_SECONDS = 30;
	
	constructor(props) {
		super(props);
		this.state = {
			unseen: 0,
			notifications: []
		}
	}

	loadNotifications() {
		fetch("core/actions/getRequestNotifications.php")
			.then(res => res.json())
			.then(data => {
				this.setState({
					alertUser: data.alertUser,
					notifications: data.notifications,
					unseen: data.unseen
				});
				if (data.alertUser) {
					// Play sound
					var audio = ReactDOM.findDOMNode(this.refs.refAudio);
					audio.play();
				}
			}).catch(err => {
				throw err;
			});
	}

	componentDidMount() {
		this.loadNotifications();
		/*
		  Now we need to make it run at a specified interval,
		  bind the getData() call to `this`, and keep a reference
		  to the invterval so we can clear it later.
		*/
		this.intervalID = setInterval(this.loadNotifications.bind(this), this.INTERVAL_IN_SECONDS * 1000);

		/**
		* request_notifications é o wrapper desse componente JSX
		 */
		$("#request_notifications").on('show.bs.dropdown', function(event) {
			// Marca as mensagens como visualizadas
			let ids = [];
			this.state.notifications.map((item) => {
				ids.push(item.id);
			});
			fetch(`core/actions/markNotificationsVisualized.php?ids=${ids}`).then(res => res.text());
		}.bind(this));
	}

	componentWillUnmount() {
		/*
		  stop getData() from continuing to run even
		  after unmounting this component
		*/
		clearInterval(this.intervalID);
	}

	pluralize(qtd) {
		if (qtd > 1) {
			return `${qtd} novos alertas`;
		} else if (qtd == 1) {
			return `${qtd} novo alerta`;
		} else {
			return "Nenhum novo alerta";
		}
	}

	render() {
		var notificationItems = this.state.notifications.map((item) => {
			return (<RequestNotificationItem
				objectId={item.objectId}
				userphoto={item.userphoto}
				username={item.username}
				datetime={item.datetime}
				type={item.type}
				event={item.event}
				seen={item.seen}
				client={item.client}
			/>);
		})

		var displayHeartbit = 'none';

		if (this.state.alertUser) {
			displayHeartbit = 'block';
		}

		return (
			<React.Fragment>
				<a id="2"
					className={"nav-link dropdown-toggle text-muted waves-effect waves-dark"}
					href="#" data-toggle="dropdown"
					aria-haspopup="true"
					aria-expanded="false">
					<i className={"mdi mdi-bell"} ></i>
					<div style={{ display: displayHeartbit }} className={"notify"}>
						<span className={"heartbit"}></span>
						<span className={"point"}></span>
					</div>
				</a>
				<div style={{ right: "0", left: 'auto' }} className={"dropdown-menu mailbox animated bounceInDown"} aria-labelledby="2">
					<ul>
						<li>
							<div className={"drop-title"}>{this.pluralize(this.state.unseen)}</div>
						</li>
						<li>
							<div className={"message-center"}>
								{notificationItems}
							</div>
						</li>
						<li>
							<a className={"nav-link text-center"} href="listar_notificacoes.php"> <strong>Ver todas</strong> <i className={"fa fa-angle-right"}></i> </a>
						</li>
					</ul>
				</div>
				<audio ref="refAudio" src="sounds/eventually.mp3"></audio>
			</React.Fragment>
		);
	}
}