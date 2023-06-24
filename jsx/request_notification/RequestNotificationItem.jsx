class RequestNotificationItem extends React.Component {
	TYPE_REQUEST = 1;
	TYPE_DEVOLUTION = 2;
	TYPE_WITHDRAWAL = 3;
	
	EVENT_REGISTER = 1;
	EVENT_CANCEL = 2;

	getEventDescription() {
		switch(parseInt(this.props.event)) {
			case this.EVENT_REGISTER: return (parseInt(this.props.type) == this.TYPE_REQUEST) ? "realizou" : "solicitou";
			case this.EVENT_CANCEL: return "cancelou";
			default: return "";
		}
	}

	getActionDescription() {
		switch(parseInt(this.props.type)) {
			case this.TYPE_REQUEST: return " um pedido";
			case this.TYPE_DEVOLUTION: return " uma devolução";
			case this.TYPE_WITHDRAWAL: return " uma retirada";
			default: return "";
		}
	}
	
	getStatusClass() {
		if (this.props.seen) {
			return "notification_status_seen float-right";
		} else {
			return "notification_status_unseen float-right";
		}
	}

	getLink() {
		switch(parseInt(this.props.type)) {
			case this.TYPE_REQUEST: return `visualizar_pedido.php?r=${this.props.objectId}`;
			case this.TYPE_DEVOLUTION: return `visualizar_devolucao.php?dev=${this.props.objectId}`;
			case this.TYPE_WITHDRAWAL: return `visualizar_retirada.php?r=${this.props.objectId}`;
			default: return "";
		}
	}

    render() {
        return (
            <a href={this.getLink()} style={{position:'relative'}}>
                <div className={"user-img"} >
                    <img src={this.props.userphoto == null ? "img/avatar.png" : this.props.userphoto} alt="user" className={"img-circle"} />
                </div>
                <div className={"mail-contnet"}>
                    <h5>{this.props.username} {this.getEventDescription() + this.getActionDescription()}</h5>
                    <span className={"mail-desc"}>{this.props.client}</span>
                    <span className={"time"} >{this.props.datetime}</span>
                </div>
                <span className={this.getStatusClass()}></span>
            </a>
        );
    }
}