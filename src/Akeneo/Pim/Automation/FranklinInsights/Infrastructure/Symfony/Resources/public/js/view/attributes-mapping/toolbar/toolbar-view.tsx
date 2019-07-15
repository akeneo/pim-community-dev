/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import * as ReactDOM from 'react-dom';

import View = require('pimui/js/view/base');
import {isAbleToCreateAttribute} from '../../../common/attribute-mapping-helper';
import AttributesMappingForFamily from '../../../model/attributes-mapping-for-family';
import Toolbar from './toolbar';
import {AttributeData} from './toolbar';

class ToolbarView extends View {
  private selectedFranklinAttributeCodes = new Set<string>();

  public configure(): any {
    super.configure();

    this.getFormModel().on('change', this.render, this);

    this.listenTo(this.getRoot(), 'franklin_attribute_selected', this.selectFranklinAttribute);
    this.listenTo(this.getRoot(), 'franklin_attribute_unselected', this.deselectFranklinAttribute);
  }

  public render() {
    if (0 === this.selectedFranklinAttributeCodes.size) {
      this.$el.hide();

      return this;
    } else {
      this.$el.show();
    }

    const model: AttributesMappingForFamily = this.getFormData();

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
          canAddToFamily: attributeMapping.exactMatchAttributeFromOtherFamily,
          selected: this.selectedFranklinAttributeCodes.has(franklinAttributeCode)
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

  private selectFranklinAttribute(franklinAttributeCode: string) {
    this.selectedFranklinAttributeCodes.add(franklinAttributeCode);

    this.render();
  }

  private deselectFranklinAttribute(franklinAttributeCode: string) {
    this.selectedFranklinAttributeCodes.delete(franklinAttributeCode);

    this.render();
  }

  private selectAllFranklinAttributes() {
    this.getRoot().trigger('select_all_franklin_attributes');

    const model: AttributesMappingForFamily = this.getFormData();
    Object.keys(model.mapping).forEach(franklinAttributeCode =>
      this.selectedFranklinAttributeCodes.add(franklinAttributeCode)
    );

    this.render();
  }

  private deselectAllFranklinAttributes() {
    this.getRoot().trigger('deselect_all_franklin_attributes');

    this.selectedFranklinAttributeCodes.clear();

    this.render();
  }

  private refreshFamilyMapping() {
    this.getRoot().trigger('refresh_family_mapping');

    this.selectedFranklinAttributeCodes.clear();

    this.render();
  }
}

export = ToolbarView;
