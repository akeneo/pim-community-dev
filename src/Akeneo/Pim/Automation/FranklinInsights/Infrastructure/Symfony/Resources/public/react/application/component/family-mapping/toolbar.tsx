/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';

import {PartialCheckbox, CheckboxState} from '../app/partial-checkbox';
import {useDispatch, useSelector} from 'react-redux';
import {
  unselectAllFranklinAttributes,
  selectAllFranklinAttributes
} from '../../action/family-mapping/franklin-attribute-selection';
import {Translate} from '../shared/translate';
import {FamilyMappingState} from '../../reducer/family-mapping';
import {BulkCreateAttributeButton} from './bulk-create-attribute-button';
import {selectAttributesThatCanBeCreated} from '../../selector/select-attributes-that-can-be-created';
import {AttributeMapping} from '../../../domain/model/attribute-mapping';
import {bulkCreateAttributes} from '../../action/family-mapping/create-attribute';
import {BulkAddToFamilyButton} from './bulk-add-to-family-button';
import {selectAttributesThatCanBeAddedToFamily} from '../../selector/select-attributes-that-can-be-added-to-family';
import {bulkAddAttributesToFamily} from '../../action/family-mapping/add-attribute-to-family';

interface Props {
  selectedFranklinAttributeCodes: string[];
}

interface FranklinAttributeToCreate {
  franklinAttributeLabel: string;
  franklinAttributeType: string;
}

export const Toolbar = ({selectedFranklinAttributeCodes}: Props) => {
  const dispatch = useDispatch();
  const franklinAttributeCodes: string[] = useSelector((state: FamilyMappingState) =>
    Object.keys(state.familyMapping.mapping)
  );
  const attributesToCreate: AttributeMapping[] = useSelector(selectAttributesThatCanBeCreated).filter(
    (attributeMapping: AttributeMapping) =>
      selectedFranklinAttributeCodes.includes(attributeMapping.franklinAttribute.code)
  );
  const attributesToAddToFamily: AttributeMapping[] = useSelector(selectAttributesThatCanBeAddedToFamily).filter(
    (attributeMapping: AttributeMapping) =>
      selectedFranklinAttributeCodes.includes(attributeMapping.franklinAttribute.code)
  );
  const familyCode = useSelector((state: FamilyMappingState) => state.familyMapping.familyCode as string);

  const handleOnCheckboxChange = (previousState: CheckboxState) => {
    CheckboxState.Checked === previousState
      ? dispatch(unselectAllFranklinAttributes())
      : dispatch(selectAllFranklinAttributes(franklinAttributeCodes));
  };

  const handleBulkCreateAttributes = () => {
    const attributes: FranklinAttributeToCreate[] = attributesToCreate.reduce(
      (attributes: FranklinAttributeToCreate[], attributeMapping: AttributeMapping) => {
        attributes.push({
          franklinAttributeLabel: attributeMapping.franklinAttribute.label,
          franklinAttributeType: attributeMapping.franklinAttribute.type
        });

        return attributes;
      },
      []
    );
    dispatch(bulkCreateAttributes(familyCode, attributes));
  };

  const handleBulkAddAttributesToFamily = () => {
    const attributeCodes: string[] = attributesToAddToFamily.reduce(
      (attributes: string[], attributeMapping: AttributeMapping) => {
        if (attributeMapping.exactMatchAttributeFromOtherFamily !== null) {
          attributes.push(attributeMapping.exactMatchAttributeFromOtherFamily);
        }

        return attributes;
      },
      []
    );
    dispatch(bulkAddAttributesToFamily(familyCode, attributeCodes));
  };

  return (
    <div className='AknFranklin-gridToolbar'>
      <PartialCheckbox
        checkboxState={getCheckboxState(franklinAttributeCodes.length, selectedFranklinAttributeCodes.length)}
        onChange={handleOnCheckboxChange}
      />
      <div className='AknMassActions-counter'>
        <Translate
          id={'akeneo_franklin_insights.entity.attributes_mapping.module.toolbar.selection_count'}
          placeholders={{count: selectedFranklinAttributeCodes.length}}
          count={selectedFranklinAttributeCodes.length}
        />
      </div>

      <div className='AknButtonList'>
        <BulkCreateAttributeButton
          attributesToCreateCount={attributesToCreate.length}
          onConfirm={handleBulkCreateAttributes}
        />
        <BulkAddToFamilyButton
          attributesToAddToFamilyCount={attributesToAddToFamily.length}
          onConfirm={handleBulkAddAttributesToFamily}
        />
      </div>
    </div>
  );
};

function getCheckboxState(attributeCount: number, selectedAttributeCount: number): CheckboxState {
  if (selectedAttributeCount === attributeCount) {
    return CheckboxState.Checked;
  }

  if (selectedAttributeCount > 0) {
    return CheckboxState.Partial;
  }

  return CheckboxState.Unchecked;
}
