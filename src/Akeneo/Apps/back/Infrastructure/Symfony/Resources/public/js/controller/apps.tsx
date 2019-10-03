import * as $ from 'jquery';
import * as React from 'react';
import * as ReactDOM from 'react-dom';

import {Apps} from '@akeneo-pim-ce/apps';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');

class AppsController extends BaseController {
  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-apps'});

    ReactDOM.render(<Apps />, this.el);

    return $.Deferred().resolve();
  }
}

export = AppsController;
