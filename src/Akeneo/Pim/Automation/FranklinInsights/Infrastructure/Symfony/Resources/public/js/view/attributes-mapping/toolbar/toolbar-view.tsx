/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import View = require('pimui/js/view/base');
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import {isAbleToAddAttributeToFamily, isAbleToCreateAttribute} from '../../../common/attribute-mapping-helper';
import AttributesMappingModel from '../../../model/attributes-mapping-model';
import Toolbar, {AttributeData} from './toolbar';

class ToolbarView extends View {
  public configure(): any {
    super.configure();

    this.getFormModel().on('change', () => this.render());

    this.listenTo(this.getRoot(), 'franklin_attribute_selected', () => this.render());
    this.listenTo(this.getRoot(), 'franklin_attribute_deselected', () => this.render());
  }

  public render() {
    const model: AttributesMappingModel = this.getFormData();
    if (undefined === model.mapping) {
      return this;
    }

    if (true === Object.values(model.selectedFranklinAttributes).find(selected => true === selected)) {
      this.$el.show();
    } else {
      this.$el.hide();

      return this;
    }

    this.renderReactToolbar(
      model.code,
      Object.entries(model.mapping).map(
        ([franklinAttributeCode, attributeMapping]): AttributeData => ({
          franklinLabel: attributeMapping.franklinAttribute.label,
          franklinType: attributeMapping.franklinAttribute.type,
          canCreate: isAbleToCreateAttribute(
            attributeMapping.attribute,
            attributeMapping.status,
            attributeMapping.canCreateAttribute
          ),
          canAddToFamily: isAbleToAddAttributeToFamily(
            attributeMapping.attribute,
            attributeMapping.status,
            attributeMapping.exactMatchAttributeFromOtherFamily
          ),
          exactMatchAttributeFromOtherFamily: attributeMapping.exactMatchAttributeFromOtherFamily,
          selected: true === model.selectedFranklinAttributes[franklinAttributeCode]
        })
      )
    );

    return this;
  }

  public remove() {
    ReactDOM.unmountComponentAtNode(this.el);
    return super.remove();
  }

  private renderReactToolbar(familyCode: string, attributes: AttributeData[]) {
    ReactDOM.render(
      <Toolbar
        familyCode={familyCode}
        attributes={attributes}
        onSelectAll={this.selectAllFranklinAttributes.bind(this)}
        onDeselectAll={this.deselectAllFranklinAttributes.bind(this)}
        onFamilyMappingUpdate={this.refreshFamilyMapping.bind(this)}
      />,
      this.el
    );
  }

  private selectAllFranklinAttributes() {
    this.getRoot().trigger('select_all_franklin_attributes');

    this.render();
  }

  private deselectAllFranklinAttributes() {
    this.getRoot().trigger('deselect_all_franklin_attributes');

    this.render();
  }

  private refreshFamilyMapping() {
    this.getRoot().trigger('refresh_family_mapping');
    this.deselectAllFranklinAttributes();
  }
}

export = ToolbarView;
