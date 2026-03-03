'use strict';

const BaseForm = require('pim/form');

type NavigationBlockConfig = {
  position: number;
  targetZone: string;
  config: {
    title: string;
  };
};

class NavigationBlock extends BaseForm {
  /**
   * {@inheritdoc}
   */
  initialize(config: NavigationBlockConfig) {
    this.config = config.config;

    super.initialize(config);
  }

  /**
   * Proxy for 'pim_menu:column:register_navigation_item' event
   *
   * {@inheritdoc}
   */
  configure() {
    this.trigger('pim_menu:column:register_navigation_section', {
      code: this.code,
      title: this.config.title,
      position: this.position,
    });
    this.onExtensions('pim_menu:column:register_navigation_item', (event: any) => {
      this.trigger('pim_menu:column:register_navigation_item', {
        ...event,
        sectionCode: this.code,
      });
    });

    return super.configure();
  }
}

export = NavigationBlock;
