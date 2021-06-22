'use strict';

import {ViewOptions} from 'backbone';

const _ = require('underscore');
const __ = require('oro/translator');
const BaseForm = require('pim/form');
const template = require('pim/template/menu/navigation-block');

type NavigationBlockConfig = {
  position: number;
  targetZone: string;
  config: {
    title: string;
  };
};

/**
 * Base extension for navigation blocks
 * A navigation block is composed of a title and a list of items, displayed in the columns.
 *
 * @copyright 2021Z Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NavigationBlock extends BaseForm {
  template;

  constructor(options?: ViewOptions<any>) {
    super({
      ...options,
      className: 'AknColumn-block',
    });

    this.template = _.template(template);
  }

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
        section: this.code,
      });
    });

    return super.configure();
  }

  /**
   * {@inheritdoc}
   */
  render() {
    this.$el.empty();

    super.render();

    if (this.$el.html().trim() !== '') {
      this.$el.prepend(
        this.template({
          title: __(this.config.title),
        })
      );
    }
    return this;
  }
}

export = NavigationBlock;
