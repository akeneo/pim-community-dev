'use strict';

import {ViewOptions} from 'backbone';

const BaseForm = require('pim/form');

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
