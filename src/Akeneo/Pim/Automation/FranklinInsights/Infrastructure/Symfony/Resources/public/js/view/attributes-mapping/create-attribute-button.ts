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
import AttributeSaver from '../../saver/attribute-saver';

const __ = require('oro/translator');

class CreateAttributeButton extends View<Model> {
  private template = _.template(`
    <button class="AknButton AknButton--ghost">
      <%= __('akeneo_franklin_insights.entity.attributes_mapping.fields.create_attribute.btn') %>
    </button>
  `);

  constructor(
    private familyCode: string,
    private franklinAttributeLabel: string,
    private franklinAttributeType: string
  ) {
    super();
  }

  public events() {
    return {
      'click button': this.onCreate
    };
  }

  public render() {
    this.$el.html(this.template({__}));

    return this;
  }

  private async onCreate() {
    const response = await AttributeSaver.create({
      familyCode: this.familyCode,
      franklinAttributeLabel: this.franklinAttributeLabel,
      franklinAttributeType: this.franklinAttributeType
    });

    this.trigger('attribute_created', response.code);
  }
}

export default CreateAttributeButton;
