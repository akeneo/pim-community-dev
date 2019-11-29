import {Apps} from '@akeneo-pim-ce/apps';
import $ from 'jquery';
import React from 'react';
import {mountReactElement, unmoundReactElement} from '../react-element-helper';

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

/**
 * Only unmount React if we are leaving the Apps context.
 * Avoid mount/unmount between route changes (legacy > react-router).
 */
const handleRouteChange = (routeName: string) => false === /^akeneo_apps_/.test(routeName) && unmoundReactElement();

class AppsController extends BaseController {
  initialize() {
    this.$el.addClass('AknApps-view');

    mediator.on('route_start', handleRouteChange);
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-apps'});

    this.el.append(mountReactElement(<Apps {...dependencies} />));

    return $.Deferred().resolve();
  }

  remove() {
    mediator.off('route_start', handleRouteChange);

    this.el.remove();

    return super.remove();
  }
}

export = AppsController;
