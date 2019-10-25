import {Apps} from '@akeneo-pim-ce/apps';
import $ from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';

const BaseController = require('pim/controller/base');

const mediator = require('oro/mediator');
const router = require('pim/router');
const translate = require('oro/translator');
const viewBuilder = require('pim/form-builder');
const messenger = require('oro/messenger');

const dependencies = {
  router,
  translate,
  viewBuilder,
  notify: messenger.notify.bind(messenger),
};

class AppsController extends BaseController {
  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-apps'});

    ReactDOM.render(<Apps {...dependencies} />, this.el);

    return $.Deferred().resolve();
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = AppsController;
