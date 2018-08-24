import * as React from 'react';
import * as ReactDOM from 'react-dom';

const viewBuilder = require('pim/form-builder');

interface PimViewProps {
  viewName: string;
  className: string;
}

export default class PimView extends React.Component<PimViewProps, {}> {
  private el: any;

  constructor(props: PimViewProps) {
    super(props);

    this.el = null;
  }

  componentDidMount() {
    this.el = ReactDOM.findDOMNode(this);
    viewBuilder.build(this.props.viewName).then((view: any) => {
      view.setElement(this.el).render();
    });
  }

  render() {
    return <div className={this.props.className} />;
  }

  componentWillUnmount() {
    this.el = null;
  }
}
