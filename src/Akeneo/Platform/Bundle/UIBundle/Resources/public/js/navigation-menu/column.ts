'use strict';

import {ViewOptions} from 'backbone';

const BaseColumn = require('pim/form/common/column');

class Column extends BaseColumn {
  sections: any[];

  constructor(options?: ViewOptions<any>) {
    super(options);

    this.sections = [];
  }

  configure() {
    this.onExtensions('pim_menu:column:register_navigation_section', this.registerNavigationSection);

    return super.configure();
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
      title: navigationItem.label,
      sectionCode: navigationItem.section,
      route: navigationItem.route,
      position: navigationItem.position,
      routeParams: navigationItem.routeParams,
    });
  }

  registerNavigationSection(navigationSection: {code: string; title: string; position: number}) {
    this.sections.push({
      code: navigationSection.code,
      title: navigationSection.title,
      position: navigationSection.position,
    });
  }
}

export = Column;
