import * as React from 'react';

const viewBuilder = require('pim/form-builder');

interface PimViewProps {
  viewName: string;
  className: string;
}

export class PimView extends React.Component<PimViewProps, {}> {
  private el: React.RefObject<HTMLDivElement>;

  constructor(props: PimViewProps) {
    super(props);

    this.el = React.createRef();
  }

  componentDidMount() {
    if (null !== this.el.current) {
      setTimeout(() => {
        viewBuilder.build(this.props.viewName).then((view: any) => {
          view.setElement(this.el.current).render();
        });
      });
    }
  }

  render() {
    return <div className={this.props.className} ref={this.el} />;
  }
}
