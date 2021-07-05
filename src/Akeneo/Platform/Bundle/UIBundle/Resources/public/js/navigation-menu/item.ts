'use strict';

import {ViewOptions} from 'backbone';

const _ = require('underscore');
const __ = require('oro/translator');
const BaseForm = require('pim/form');
const router = require('pim/router');
const mediator = require('oro/mediator');

type ItemConfig = {
  position: number;
  config: {
    title: string;
    to: string;
  };
};

class Item extends BaseForm {
  active;

  constructor(options?: ViewOptions<any>) {
    super({
      ...options,
    });

    this.active = false;
  }

  /**
   * {@inheritdoc}
   */
  initialize(config: ItemConfig) {
    this.config = config.config;

    mediator.on('pim_menu:highlight:item', this.highlight, this);
    mediator.on('pim_menu:redirect:item', this.redirect, this);

    super.initialize(config);
  }

  /**
   * On configure, this module triggers an event to register it to tabs.
   *
   * {@inheritdoc}
   */
  configure() {
    this.getRoot().on('pim_menu:item:update_route_params', this.updateRouteParams, this);
    this.trigger('pim_menu:column:register_navigation_item', {
      code: this.code,
      route: this.getRoute(),
      title: this.getLabel(),
      position: this.position,
      routeParams: this.getRouteParams(),
    });

    return super.configure();
  }

  /**
   * Redirect the user to the config destination
   *
   * @param {Event} event
   */
  redirect(event: any) {
    if (!_.has(event, 'extension')) {
      event.stopPropagation();
      event.preventDefault();
    }

    if (!(event.metaKey || event.ctrlKey) && (!_.has(event, 'extension') || event.extension === this.code)) {
      router.redirectToRoute(this.getRoute(), this.getRouteParams());
    }
  }

  /**
   * Returns the route of the tab.
   *
   * @returns {string|undefined}
   */
  getRoute() {
    return this.config.to;
  }

  /**
   * Returns the route parameters.
   *
   * @returns {Object}
   */
  getRouteParams() {
    return this.config.routeParams !== 'undefined' ? this.config.routeParams : {};
  }

  /**
   * Returns the displayed label of the tab
   *
   * @returns {string}
   */
  getLabel() {
    return __(this.config.title);
  }

  /**
   * Highlight or un-highlight item
   *
   * @param {Event}  event
   * @param {string} event.extension The extension code to highlight
   */
  highlight(event: any) {
    this.active = event.extension === this.code;

    this.render();
  }

  /**
   * Update the route params of the matching route.
   *
   * @param {string} payload
   * @param {string} payload.route
   * @param {string} payload.routeParams
   */
  updateRouteParams(payload: {route: string; routeParams: string}) {
    if (this.config.to !== payload.route) {
      return;
    }

    this.config.routeParams = payload.routeParams;
  }
}

export = Item;
