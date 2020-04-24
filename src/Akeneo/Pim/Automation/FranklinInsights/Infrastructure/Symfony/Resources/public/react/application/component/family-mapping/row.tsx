/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {ChangeEvent, useContext} from 'react';
import {useDispatch, useSelector} from 'react-redux';

import {areMappingTypesCompatible} from '../../../domain/are-mapping-types-compatible';
import {ATTRIBUTE_TYPES_WITH_OPTIONS} from '../../../domain/attribute-types-with-option';
import {isAlreadyMapped} from '../../../domain/is-already-mapped';
import {AttributeMapping} from '../../../domain/model/attribute-mapping';
import {AttributeMappingStatus} from '../../../domain/model/attribute-mapping-status.enum';
import {unmapFranklinAttribute} from '../../action-creator/family-mapping/unmap-franklin-attribute';
import {addAttributeToFamily} from '../../action/family-mapping/add-attribute-to-family';
import {createAttribute} from '../../action/family-mapping/create-attribute';
import {deactivateFranklinAttributeMapping} from '../../action/family-mapping/deactivate-franklin-attribute-mapping';
import {
  selectFranklinAttribute,
  unselectFranklinAttribute
} from '../../action/family-mapping/franklin-attribute-selection';
import {SecurityContext} from '../../context/security-context';
import {FamilyMappingState} from '../../reducer/family-mapping';
import {selectAttribute} from '../../thunk/select-attribute';
import {GhostButton} from '../app/buttons';
import {SecondaryActionLink} from '../app/secondary-action-link';
import {SecondaryActionsDropdown} from '../app/secondary-actions-dropdown';
import {Translate} from '../shared/translate';
import {AttributeOptionsMappingButton} from './attribute-options-mapping-button';
import {AttributeSelector} from './attribute-selector';
import {FranklinAttributeDetails} from './franklin-attribute-details';

interface Props {
  franklinAttributeCode: string;
  mapping: AttributeMapping;
}

export const Row = ({franklinAttributeCode, mapping}: Props) => {
  const dispatch = useDispatch();
  const isGranted = useContext(SecurityContext);

  const familyCode = useSelector((state: FamilyMappingState) => state.familyMapping.familyCode as string);
  const isSelected = useSelector(
    (state: FamilyMappingState) =>
      undefined !==
      state.selectedFranklinAttributeCodes.find(
        (selectedFranklinAttributeCode: string) => selectedFranklinAttributeCode === franklinAttributeCode
      )
  );
  const isAttributeAlreadyMapped = useSelector((state: FamilyMappingState) =>
    isAlreadyMapped(state.familyMapping.mapping, franklinAttributeCode, mapping.attribute)
  );
  const attributes: any = useSelector((state: FamilyMappingState) => state.attributes.attributes);

  const handleSelectFranklinAttributeRow = (event: ChangeEvent<HTMLInputElement>) => {
    if (true === event.target.checked) {
      dispatch(selectFranklinAttribute(franklinAttributeCode));
    } else {
      dispatch(unselectFranklinAttribute(franklinAttributeCode));
    }
  };

  const handleSelectAttribute = (attributeCode?: string) => {
    if (undefined !== attributeCode) {
      dispatch(selectAttribute(familyCode, franklinAttributeCode, attributeCode));
    } else {
      dispatch(unmapFranklinAttribute(familyCode, franklinAttributeCode));
    }
  };

  const handleCreateAttribute = () =>
    dispatch(
      createAttribute(
        familyCode,
        franklinAttributeCode,
        mapping.franklinAttribute.type,
        mapping.franklinAttribute.label
      )
    );

  const handleAddAttributeToFamily = () =>
    dispatch(
      addAttributeToFamily(familyCode, franklinAttributeCode, mapping.exactMatchAttributeFromOtherFamily as string)
    );

  const handleDeactivateFranklinAttributeMapping = () =>
    dispatch(deactivateFranklinAttributeMapping(familyCode, franklinAttributeCode));

  return (
    <tr className={'AknGrid-bodyRow' + (true === isSelected ? ' AknGrid-bodyRow--selected' : '')}>
      <td className='AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox'>
        <input type='checkbox' checked={isSelected} onChange={handleSelectFranklinAttributeRow} />
      </td>

      <td className='AknGrid-bodyCell franklin-attribute'>
        <FranklinAttributeDetails
          type={mapping.franklinAttribute.type}
          label={mapping.franklinAttribute.label}
          samples={mapping.franklinAttribute.summary}
        />
      </td>

      <td className='AknGrid-bodyCell attribute-selector' data-franklin-attribute-code={franklinAttributeCode}>
        <div className={'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline'}>
          <AttributeSelector
            selectedAttributeCode={mapping.attribute || undefined}
            suggestedAttributeCodes={mapping.suggestions}
            franklinAttributeCode={franklinAttributeCode}
            onSelect={handleSelectAttribute}
            hasError={isAttributeAlreadyMapped}
          />

          {null !== mapping.attribute &&
            undefined !== attributes[mapping.attribute] &&
            undefined !== ATTRIBUTE_TYPES_WITH_OPTIONS.find(type => type === attributes[mapping.attribute!].type) && (
              <AttributeOptionsMappingButton
                familyCode={familyCode}
                attributeCode={mapping.attribute}
                franklinAttributeCode={franklinAttributeCode}
              />
            )}
        </div>
      </td>

      <td className='AknGrid-bodyCell'>
        {isGranted('pim_enrich_attribute_create') &&
          isGranted('pim_enrich_family_edit_attributes') &&
          null === mapping.attribute &&
          true === mapping.canCreateAttribute && (
            <GhostButton onClick={handleCreateAttribute} classNames={['create-attribute-button']}>
              <Translate id='akeneo_franklin_insights.entity.attributes_mapping.fields.create_attribute.btn' />
            </GhostButton>
          )}

        {isGranted('pim_enrich_family_edit_attributes') &&
          null === mapping.attribute &&
          null !== mapping.exactMatchAttributeFromOtherFamily && (
            <GhostButton onClick={handleAddAttributeToFamily}>
              <Translate id='akeneo_franklin_insights.entity.attributes_mapping.fields.add_attribute_to_family.btn' />
            </GhostButton>
          )}

        {null !== mapping.attribute &&
          undefined !== attributes[mapping.attribute] &&
          !areMappingTypesCompatible(mapping.franklinAttribute.type, attributes[mapping.attribute].type) && (
            <span className='AknFieldContainer-validationWarning'>
              <Translate id='akeneo_franklin_insights.entity.attributes_mapping.module.index.types_mismatch_warning' />
            </span>
          )}
      </td>

      <td className='AknGrid-bodyCell AknGrid-bodyCell--right'>
        {mapping.status !== AttributeMappingStatus.INACTIVE && (
          <SecondaryActionsDropdown>
            <SecondaryActionLink onClick={handleDeactivateFranklinAttributeMapping}>
              <Translate id='akeneo_franklin_insights.entity.attributes_mapping.fields.secondary_actions.deactivate_franklin_attribute' />
            </SecondaryActionLink>
          </SecondaryActionsDropdown>
        )}
      </td>
    </tr>
  );
};
