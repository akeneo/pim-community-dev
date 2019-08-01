/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {Component} from 'react';

import {SelectButton, SelectionState} from '../../../common/select-button';
import AddAttributeToFamily from '../../../saver/add-attribute-to-family';
import AttributeSaver from '../../../saver/attribute-saver';
import BulkAddToFamilyButton from '../add-to-family/bulk-add-to-family-button';
import BulkCreateAttributeButton from '../create-attribute/bulk-create-attribute-button';

const __ = require('oro/translator');

export interface AttributeData {
  franklinLabel: string;
  franklinType: string;
  canCreate: boolean;
  canAddToFamily: boolean;
  exactMatchAttributeFromOtherFamily: string | null;
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
          <BulkCreateAttributeButton
            count={this.getAttributesToCreate(selectedAttributes).length}
            onClick={this.bulkCreateAttribute.bind(this)}
          />

          <BulkAddToFamilyButton
            count={this.getAttributesToAddToFamily(selectedAttributes).length}
            onClick={this.bulkAddAttributeToFamily.bind(this)}
          />
        </div>
      </>
    );
  }

  private getAttributesToCreate(selectedAttributes: AttributeData[]): AttributeData[] {
    return selectedAttributes.filter(attribute => attribute.canCreate);
  }

  private getAttributesToAddToFamily(selectedAttributes: AttributeData[]): AttributeData[] {
    return selectedAttributes.filter(attribute => attribute.canAddToFamily);
  }

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

  private async bulkCreateAttribute() {
    const attributes = this.getAttributesToCreate(this.selectedFranklinAttributes).map(attribute => ({
      franklinAttributeLabel: attribute.franklinLabel,
      franklinAttributeType: attribute.franklinType
    }));

    await AttributeSaver.bulkCreate({
      familyCode: this.props.familyCode,
      attributes
    });

    this.props.onFamilyMappingUpdate();
  }

  private async bulkAddAttributeToFamily() {
    const attributeCodes = this.getAttributesToAddToFamily(this.selectedFranklinAttributes).map(
      attribute => attribute.exactMatchAttributeFromOtherFamily
    ) as string[];

    await AddAttributeToFamily.bulkAdd({
      familyCode: this.props.familyCode,
      attributeCodes
    });

    this.props.onFamilyMappingUpdate();
  }
}

export default Toolbar;
