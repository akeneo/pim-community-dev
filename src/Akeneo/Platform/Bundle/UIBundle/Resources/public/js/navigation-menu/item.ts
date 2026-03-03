'use strict';

const __ = require('oro/translator');
const BaseForm = require('pim/form');

type ItemConfig = {
  position: number;
  config: {
    title: string;
    to: string;
    disabled?: boolean;
    new?: boolean;
  };
};

class Item extends BaseForm {
  /**
   * {@inheritdoc}
   */
  initialize(config: ItemConfig) {
    this.config = config.config;
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
      disabled: this.config.disabled,
      new: this.config.new,
    });

    return super.configure();
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
