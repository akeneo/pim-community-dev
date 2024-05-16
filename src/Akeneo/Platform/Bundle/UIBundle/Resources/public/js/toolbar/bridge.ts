const BaseView = require('pimui/js/view/base');
const mediator = require('oro/mediator');

import ReactDOM from 'react-dom';
import {ToolBar} from './ToolBar'

class Bridge extends BaseView {
  private currentRoute?: string;

  constructor(options: any) {
    super(options);
  }

  configure(): JQueryPromise<any> {
    this.listenTo(mediator, 'route_start', (route: string) => {
      this.currentRoute = route;
      this.render();
    });

    return BaseView.prototype.configure.apply(this, arguments);
  }

  /**
   * {@inheritdoc}
   */
  render() {
    this.renderReact(
      ToolBar,
      {route: this.currentRoute},
      this.el
    );

    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = Bridge;
