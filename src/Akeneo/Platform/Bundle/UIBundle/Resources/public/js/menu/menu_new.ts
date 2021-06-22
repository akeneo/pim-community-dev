'use strict';

import {ViewOptions} from 'backbone';

const BaseForm = require('pim/form');
const _ = require('underscore');
const template = require('pim/template/menu/menu');

/**
 * Base extension for menu
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Menu extends BaseForm {
  template = _.template(template);

  constructor(options?: ViewOptions<any>) {
    super({
      ...options,
      className: 'AknHeader',
    });
  }

  /**
   * {@inheritdoc}
   */
  render() {
    this.$el.empty().append(this.template());

    super.render(arguments);

    return this;
  }

  /**
   * {@inheritdoc}
   */
  renderExtension(extension: any) {
    if (
      !_.isEmpty(extension.options.config) &&
      (!extension.options.config.to || extension.options.config.isLandingSectionPage) &&
      _.isFunction(extension.hasChildren) &&
      !extension.hasChildren()
    ) {
      return;
    }

    super.renderExtension(extension);
  }
}

export = Menu;
