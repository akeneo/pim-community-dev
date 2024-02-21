'use strict';

import {ViewOptions} from 'backbone';

const _ = require('underscore');
const BaseForm = require('pim/form');
const router = require('pim/router');
const mediator = require('oro/mediator');

type TabConfig = {
  position: number;
  targetZone: string;
  config: {
    title: string;
  };
  iconModifier: string;
  to: string;
};

class Tab extends BaseForm {
  items: any[];

  constructor(options?: ViewOptions<any>) {
    super({
      ...options,
    });

    this.items = [];
  }

  /**
   * {@inheritdoc}
   */
  initialize(config: TabConfig) {
    this.config = {
      ...config.config,
    };
    this.items = [];

    mediator.on('pim_menu:redirect:tab', this.redirect, this);

    super.initialize(config);
  }

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this.getRoot(), 'pim_menu:register_item', this.registerItem);

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

    if (
      !(event.metaKey || event.ctrlKey) &&
      (!_.has(event, 'extension') || event.extension === this.code) &&
      undefined !== this.getRoute()
    ) {
      router.redirectToRoute(this.getRoute(), this.getRouteParams());
    }
  }

  /**
   * Returns the route of the tab.
   *
   * There is 2 cases here:
   * - The configuration contains a `to` element, so we did a simple redirect to this route.
   * - There is no configuration, so we need to get the first available element of the associated column.
   *   For this, we simply register all the items of the column, sort them by priority then take the first
   *   one.
   *
   * @returns {string|undefined}
   */
  getRoute() {
    if (undefined !== this.config.to) {
      return this.config.to;
    } else {
      return _.first(_.sortBy(this.items, 'position')).route;
    }
  }

  /**
   * Returns the route parameters.
   *
   * @returns {json}
   */
  getRouteParams() {
    if (undefined !== this.config.to) {
      return this.config.routeParams !== 'undefined' ? this.config.routeParams : {};
    } else {
      return _.first(_.sortBy(this.items, 'position')).routeParams;
    }
  }

  /**
   * Registers a new item attached to this tab.
   *
   * @param {Event}  event
   * @param {string} event.route
   * @param {number} event.position
   */
  registerItem(event: any) {
    if (event.target === this.code) {
      this.items.push(event);
    }
  }

  /**
   * Does this tab have children elements
   *
   * @return {Boolean}
   */
  hasChildren() {
    return 0 < this.items.length;
  }
}

export = Tab;
