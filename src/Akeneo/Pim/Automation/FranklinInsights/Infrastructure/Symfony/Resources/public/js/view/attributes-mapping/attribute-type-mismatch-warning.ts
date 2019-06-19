/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {Model, View} from 'backbone';
import * as _ from 'underscore';

const template = require('akeneo/franklin-insights/template/settings/attributes-mapping/warning');
const __ = require('oro/translator');

class AttributeTypeMismatchWarning extends View<Model> {
  private template = _.template(template);

  public render() {
    this.$el.html(
      this.template({
        message: __('akeneo_franklin_insights.entity.attributes_mapping.module.index.types_mismatch_warning')
      })
    );

    return this;
  }
}

export default AttributeTypeMismatchWarning;
