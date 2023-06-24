class InfoGraphic extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      cancelled : false,
      steps : []
    }
  }
  componentDidMount() {
    fetch("core/actions/getRequestHistory.php?r=" + this.props.request)
        .then(res => res.json())
        .then(data => {
          this.setState({
            cancelled : data.CANCELLED,
            steps : data.aaData
          });
        }).catch(err => {
          throw err;
        });
  }
  render() {
    const LINE = "L";
    const RETURNED = "U25";
    const CANCELLED = "U22";
    const ATTENDED = "U24";
    const FINISHED = "U26";
    const ACTION_ID = 0;
    const ACTIVE = 1;
    const FILE_INDEX = 2;

    const steps = Array.from(this.state.steps);
    const cancelledRequest = this.state.cancelled;

    const stepItems = steps.map(function(step, index, elements) {
        if(step[ACTION_ID] != FINISHED) {
          if(step[ACTION_ID] == LINE) {
            // Se a devolução está ativa
            if(index > 0 && (elements[index-1][ACTION_ID] == RETURNED || (elements[index-1][ACTION_ID] == RETURNING && elements[index-1][PERCENTAGE_INDEX] > 0)) && elements[index-1][ACTIVE]) {
              return <InfoGraphicLine active={true} />
            }

            return <InfoGraphicLine active={step[ACTIVE]} />
          }

          // Se esse é ativo e o proximo nao, fica pulsanso
          let shouldPulse = false;
          if(!cancelledRequest && (index + 2) < elements.length && step[ACTIVE] && !elements[index + 2][ACTIVE] && elements[index + 2][ACTION_ID] != FINISHED){
            shouldPulse = true;
          }
          
          // Verifica se terá link p/ PDF
          let fileName = null;
          if(step[ACTION_ID] == ATTENDED) {
              fileName = step[FILE_INDEX];
          }

          return <InfoGraphicPoint action={step} pulse={shouldPulse} file={fileName}/>
        } else {// FINISHED
          return (
            <React.Fragment>
            <InfoGraphicPoint action={["RECEIPT", elements[index-2][ACTIVE]]} pulse={!step[ACTIVE] && !cancelledRequest && elements[index-2][ACTIVE]}/>
            <InfoGraphicLine active={elements[index-1][ACTIVE]} />
            <InfoGraphicPoint action={step} file={step[FILE_INDEX]}/>
            </React.Fragment>);
          }
        });
    return (
    <React.Fragment>
      <section class="containerInfo">
      {
          stepItems.length > 0 ? stepItems : ""
      }
      </section>
      {
        this.state.cancelled ?
        <div class="cancel_step">
          <img class='cancel_line' src="js/components/infographic/linha_cancelar.svg" alt="Linha cancelar." />
          <img class='cancel_balloon' src="js/components/infographic/botao_cancelar.svg" alt="Linha cancelar." />
        </div>
         : ""
      }
    </React.Fragment>
  );

  }
}