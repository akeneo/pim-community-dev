/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as Backbone from 'backbone';
import * as React from 'react';
import {Component} from 'react';

import ActionButton from '../../../common/action-button';
import {SelectButton, SelectionState} from '../../../common/select-button';
import AttributeSaver from '../../../saver/attribute-saver';

const __ = require('oro/translator');

export interface AttributeData {
  franklinLabel: string;
  franklinType: string;
  canCreate: boolean;
  canAddToFamily: string | null;
  selected: boolean;
}

interface Props {
  familyCode: string;
  attributes: AttributeData[];
  onSelectAll: () => void;
  onDeselectAll: () => void;
  onFamilyMappingUpdate: () => void;
}

export class Toolbar extends Component<Props> {
  get selectedFranklinAttributes() {
    return this.props.attributes.filter(attribute => true === attribute.selected);
  }

  public render() {
    const selectedAttributes = this.selectedFranklinAttributes;

    return (
      <>
        <SelectButton
          selectionState={this.getSelectionState(this.props.attributes.length, selectedAttributes.length)}
          onChange={this.onSelectChange.bind(this)}
        />

        <div className='AknMassActions-counter'>
          {__(
            'akeneo_franklin_insights.entity.attributes_mapping.module.toolbar.selection_count',
            {count: selectedAttributes.length},
            selectedAttributes.length
          )}
        </div>

        <div className='AknButtonList'>
          <ActionButton
            className='AknButtonList-item'
            label={__('akeneo_franklin_insights.entity.attributes_mapping.module.toolbar.bulk_create_attribute')}
            count={this.getAttributesToCreate(selectedAttributes).length}
            onClick={this.bulkCreateAttribute.bind(this)}
          />

          {/* <ActionButton
            className='AknButtonList-item'
            label={__('akeneo_franklin_insights.entity.attributes_mapping.module.toolbar.bulk_add_to_family')}
            count={this.getAttributesToAddToFamily(selectedAttributes).length}
            onClick={this.bulkAddAttributeToFamily.bind(this)}
          /> */}
        </div>
      </>
    );
  }

  private getAttributesToCreate(selectedAttributes: AttributeData[]): AttributeData[] {
    return selectedAttributes.filter(attribute => !!attribute.canCreate);
  }

  // private getAttributesToAddToFamily(selectedAttributes: AttributeData[]): AttributeData[] {
  //   return selectedAttributes.filter(attribute => !!attribute.canAddToFamily);
  // }

  private getSelectionState(attributeCount: number, selectedAttributeCount: number): SelectionState {
    if (selectedAttributeCount === attributeCount) {
      return SelectionState.Selected;
    }
    if (selectedAttributeCount > 0 && selectedAttributeCount !== attributeCount) {
      return SelectionState.Partial;
    }

    return SelectionState.Deselected;
  }

  private onSelectChange(state: SelectionState) {
    if (state === SelectionState.Selected) {
      this.props.onSelectAll();
    }
    if (state === SelectionState.Deselected) {
      this.props.onDeselectAll();
    }
  }

  private openBulkCreateAttributeConfirmationModal(count: number): Promise<void> {
    const modal = new (Backbone as any).BootstrapModal({
      picture: 'illustrations/Attribute.svg',
      title: __('akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.title', {count}, count),
      subtitle: __('akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.subtitle'),
      innerDescription: __(
        'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.description',
        undefined,
        count
      ),
      content: `
        <div class="AknMessageBox AknMessageBox--warning AknMessageBox--withIcon">
          ${__(
            'akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.warning',
            {count},
            count
          )}
        </div>
      `,
      okText: __('akeneo_franklin_insights.entity.attributes_mapping.modal.bulk_create_attribute.ok'),
      cancelText: ''
    });

    modal.open();

    return new Promise((resolve, reject) => {
      modal.listenTo(modal, 'ok', resolve);
      modal.listenTo(modal, 'cancel', reject);
    });
  }

  private async bulkCreateAttribute() {
    const attributes = this.getAttributesToCreate(this.selectedFranklinAttributes).map(attribute => ({
      franklinAttributeLabel: attribute.franklinLabel,
      franklinAttributeType: attribute.franklinType
    }));

    await this.openBulkCreateAttributeConfirmationModal(attributes.length);

    await AttributeSaver.bulkCreate({
      familyCode: this.props.familyCode,
      attributes
    });

    this.props.onFamilyMappingUpdate();
  }

  // private bulkAddAttributeToFamily() {}
}

export default Toolbar;
