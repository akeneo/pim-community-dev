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
import AddAttributeToFamily from '../../saver/add-attribute-to-family';

const __ = require('oro/translator');
const SecurityContext = require('pim/security-context');

class AddAttributeToFamilyButton extends View<Model>
{
  private template = _.template(`
    <button class="AknButton  AknButton--ghost">
      <%= __('akeneo_franklin_insights.entity.attributes_mapping.fields.add_attribute_to_family.btn') %>
    </button>
  `);

  constructor(
    private familyCode: string,
    private attributeCode: string
  ) {
    super();
  }

  public events() {
    return {
      'click button': this.add
    };
  }

  public render() {
    if (false === this.isGranted()) {
      return this;
    }

    this.$el.html(this.template({__}));

    return this;
  }

  private async add() {
    const response = await AddAttributeToFamily.add({
      familyCode: this.familyCode,
      attributeCode: this.attributeCode
    });

    this.trigger('attribute_added_to_family', response.code);
  }

  private isGranted() {
    return (SecurityContext.isGranted('pim_enrich_family_edit_attributes'));
  }
}

export default AddAttributeToFamilyButton;
