import * as $ from 'jquery';
import * as React from 'react';
import * as ReactDOM from 'react-dom';

const BaseController = require('pim/controller/base');

class AppsListController extends BaseController {
  renderRoute() {
    ReactDOM.render(
      <>Hello world!</>,
      this.el
    );

    return $.Deferred().resolve();
  }
}

export = AppsListController;
