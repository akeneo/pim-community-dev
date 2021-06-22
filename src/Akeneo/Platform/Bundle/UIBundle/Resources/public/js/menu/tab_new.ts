'use strict';

import React from 'react';
import {MainNavigationItem, CardIcon} from 'akeneo-design-system';
import {ViewOptions} from 'backbone';

const _ = require('underscore');
const __ = require('oro/translator');
const BaseForm = require('pim/form');
const router = require('pim/router');
const template = require('pim/template/menu/tab');
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

/**
 * Base extension for tab
 * This represents a main tab of the application, associated with icon, text and column.
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Tab extends BaseForm {
  template;
  active;
  items: any[];
  sections: any[];

  constructor(options?: ViewOptions<any>) {
    super({
      ...options,
      events: {
        click: 'redirect',
      },
      className: 'AknHeader-menuItemContainer',
    });

    this.template = _.template(template);
    this.items = [];
    this.sections = [];
    this.active = false;
  }

  /**
   * {@inheritdoc}
   */
  initialize(config: TabConfig) {
    this.config = {
      // Define the page url (config.to) as the landing page for the section of menu (ex: pim-menu-settings)
      isLandingSectionPage: false,
      ...config.config,
    };
    this.items = [];
    this.sections = [];

    mediator.on('pim_menu:highlight:tab', this.highlight, this);
    mediator.on('pim_menu:redirect:tab', this.redirect, this);

    super.initialize(config);
  }

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this.getRoot(), 'pim_menu:register_item', this.registerItem);
    this.listenTo(this.getRoot(), 'pim_menu:register_section', this.registerSection);

    return super.configure();
  }

  /**
   * {@inheritdoc}
   */
  render() {
    if ((!this.config.to || this.config.isLandingSectionPage) && !this.hasChildren()) {
      return this;
    }

    const icon = React.createElement(CardIcon, null);
    this.renderReact(
      MainNavigationItem,
      {
        children: this.getLabel(),
        icon: icon,
        onClick: (event: any) => this.redirect(event.nativeEvent),
        active: this.active,
      },
      this.el
    );

    return super.render();
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
   * Returns the displayed label of the tab
   *
   * @returns {string}
   */
  getLabel() {
    return __(this.config.title);
  }

  /**
   * Highlight or un-highlight tab
   *
   * @param {Event} event
   * @param {string} event.extension The extension code to highlight
   */
  highlight(event: any) {
    this.active = event.extension === this.code;

    this.render();
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

  registerSection(event: any) {
    if (event.target === this.code) {
      this.sections.push(event);
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
