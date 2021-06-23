'use strict';

import {ViewOptions} from 'backbone';

const BaseColumn = require('pim/form/common/column');
const router = require('pim/router');
const mediator = require('oro/mediator');

/**
 * Extension for menu columns
 * This extends the default column and adds some behaviors only used in the menu context (visibility)
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Column extends BaseColumn {
  active: boolean;
  isVisible: boolean;

  constructor(options?: ViewOptions<any>) {
    super(options);

    this.active = false;
    this.isVisible = true;
  }

  /**
   * {@inheritdoc}
   */
  initialize(meta: {config: {navigationTitle: string; stateCode: string}}) {
    mediator.on('pim_menu:highlight:tab', this.highlight, this);
    mediator.on('pim_menu:hide', this.hide, this);

    super.initialize(meta);
  }

  configure() {
    this.onExtensions('pim_menu:column:register_navigation_section', this.registerNavigationSection);

    return super.configure();
  }

  /**
   * {@inheritdoc}
   */
  render() {
    if (this.active && this.isVisible) {
      return super.render();
    } else {
      return this.$el.empty();
    }
  }

  /**
   * Highlight or un-highlight tab
   *
   * @param {Event} event
   * @param {string} event.extension The extension code to highlight
   * @param {string} event.columnExtension The extension code of the column to activate
   */
  highlight(event: {extension: string; columnExtension?: string}) {
    if (event.columnExtension) {
      this.active = event.columnExtension === this.code;
    } else {
      this.active = event.extension === this.getTab();
    }
    this.isVisible = true;

    this.render();
  }

  hide(menuIdentifier: string) {
    if (this.code === menuIdentifier) {
      this.isVisible = false;
    }

    this.render();
  }

  /**
   * Returns the code of the attached tab
   *
   * @returns {string}
   */
  getTab() {
    return this.config.tab;
  }

  /**
   * The DOM element contains a `data-tab` attribute for compatibility with tab Bootstram tabs.
   *
   * {@inheritdoc}
   */
  redirect(event: any) {
    const item = this.findNavigationItemByRoute(event.currentTarget.dataset.tab);
    if (undefined === item) {
      throw new Error(`Navigation Item for route "${event.currentTarget.dataset.tab}" not found.`);
    }
    router.redirectToRoute(item.route, item.routeParams);
  }

  /**
   * Registers a new item to display on navigation template
   *
   * @param {Event}    navigationItem
   * @param {string}   navigationItem.label
   * @param {function} navigationItem.isVisible
   * @param {string}   navigationItem.route
   * @param {number}   navigationItem.position
   */
  registerNavigationItem(navigationItem: {
    code: string;
    label: string;
    section: string;
    route: string;
    position: number;
    routeParams: object;
  }) {
    super.registerNavigationItem(navigationItem);

    this.getRoot().trigger('pim_menu:register_item', {
      code: navigationItem.code,
      target: this.getTab(),
      label: navigationItem.label,
      section: navigationItem.section,
      route: navigationItem.route,
      position: navigationItem.position,
      routeParams: navigationItem.routeParams,
    });
  }

  registerNavigationSection(navigationSection: {code: string; title: string; position: number}) {
    this.getRoot().trigger('pim_menu:register_section', {
      target: this.getTab(),
      code: navigationSection.code,
      title: navigationSection.title,
      position: navigationSection.position,
    });
  }
}

export = Column;
