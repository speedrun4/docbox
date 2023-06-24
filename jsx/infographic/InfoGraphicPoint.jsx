const ACTION_ID = 0;
const ACTIVE = 1;
const FILE_INDEX = 2;
const PERCENTAGE_INDEX = 2;

const OPENED = "I";
const SENDING = "U23";
const ATTENDED = "U24";
const RETURNED = "U25";
const RETURNING = "U27";
const FINISHED = "U26";
const CANCELLED = "U22";
const RECEIPT = "RECEIPT";

class InfoGraphicPoint extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const action = this.props.action;
    const pulse = this.props.pulse;
    const file =  this.props.file;

    var spanClasses = spanClassNames(action);
    var ballClasses = ballClassNames(action, pulse);
    var pointEvent = getPointEvent(action[ACTION_ID], file);

    return (
      <div class="item">
        <span className={spanClasses}>{getPointName(action[ACTION_ID])}</span>
        <div className={ballClasses} onClick={pointEvent}></div>
      </div>);
  }
}
function showFile(file) {
    // console.log(file);
}

function getPointEvent(action_id, file) {
  if(action_id == ATTENDED) return () => { window.open("./request_files/" + file, "_blank")};
  // if(action_id == RETURNED) return "Devolução";
  // if(action_id == RETURNING) return "Devolvendo";
  // if(action_id == FINISHED) return "Finalizado";
  // if(action_id == RECEIPT) return "Comprovando";
  return ()=>{};
}

function getPointName(action_id) {
  if(action_id == OPENED) return "Abriu pedido";
  if(action_id == SENDING) return "Enviando";
  if(action_id == ATTENDED) return "Entregue";
  if(action_id == RETURNED) return "Devolução";
  if(action_id == RETURNING) return "Devolvendo";
  if(action_id == FINISHED) return "Finalizado";
  if(action_id == RECEIPT) return "Comprovando";
  return "";
}
function spanClassNames(action) {
  let names = ['step_name'];
  if (action[ACTIVE]) names.push('step_name--active');
  return names.join(' ');
}

function ballClassNames(action, pulse) {
  if(action[ACTION_ID] == FINISHED) {
    return "pin_image pointer-cursor";
  }

  if(action[ACTION_ID] == RETURNING) {
    var percentage = parseInt(Math.ceil(action[PERCENTAGE_INDEX] / 5) * 5);
    if(percentage < 100) {
      return "ball_loading ball_loading--" + percentage;
    }
  }

  let names = ['ball_action'];
  if(action[ACTION_ID] == SENDING || action[ACTION_ID] == ATTENDED || action[ACTION_ID] == RECEIPT) {
    names.push('ball_action--small');
  }
  if (action[ACTIVE]) names.push('ball_action--active');
  if(pulse) {
    names.push('small-pulse');
  }
  if(action[FILE_INDEX]) {
      names.push('pointer-cursor');
  }
  return names.join(' ');
}
