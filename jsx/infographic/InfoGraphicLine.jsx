class InfoGraphicLine extends React.Component {
  constructor(props) {
    super(props);
  }
  render() {
    return (
      <div class='item'>
      {
      this.props.active ?
        <img class='step_line' src="js/components/infographic/linha_ativa.svg" alt="Caminho não percorrido." />
      :
        <img class='step_line' src="js/components/infographic/linha_inativa_1.svg" alt="Caminho não percorrido." />
      }
      </div>);
  }
}
