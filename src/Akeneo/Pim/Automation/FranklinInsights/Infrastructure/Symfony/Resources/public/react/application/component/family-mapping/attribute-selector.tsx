/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {useContext, useMemo} from 'react';
import {useSelector} from 'react-redux';

import {Attribute} from '../../../domain/model/attribute';
import {AttributeGroup} from '../../../domain/model/attribute-group';
import {UserContext} from '../../context/user-context';
import {getLabel} from '../../get-label';
import {FamilyMappingState} from '../../reducer/family-mapping';
import {Select2} from '../app/select2';
import {FrontAttributeMappingStatus} from '../../../domain/model/front-attribute-mapping-status.enum';
import {Translate} from '../shared/translate';

interface Props {
  selectedAttributeCode?: string;
  suggestedAttributeCodes: string[];
  franklinAttributeCode: string;
  hasError?: boolean;
  onSelect: (attributeCode: string) => void;
}

export const AttributeSelector = ({
  selectedAttributeCode,
  suggestedAttributeCodes,
  franklinAttributeCode,
  hasError,
  onSelect
}: Props) => {
  const user = useContext(UserContext);

  const attributes = useSelector((state: FamilyMappingState) => state.attributes.attributes);
  const attributeGroups = useSelector((state: FamilyMappingState) => state.attributeGroups);
  const attributesMappingStatus = useSelector((state: FamilyMappingState) => state.attributesMappingStatus);

  const attributesWithoutSuggestions = Object.values(attributes)
    .filter(attribute => !suggestedAttributeCodes.includes(attribute.code))
    .map(attribute => ({
      id: attribute.code,
      text: getLabel(attribute.labels, user.catalogLocale, attribute.code)
    }));

  suggestedAttributeCodes = suggestedAttributeCodes.filter((attributeCode: string) =>
    attributes.hasOwnProperty(attributeCode)
  );

  const suggestedAttributes = suggestedAttributeCodes.map((attributeCode: string) => {
    const attribute = Object.values(attributes).filter(attribute => attribute.code === attributeCode)[0];
    return {
      id: attributeCode,
      text: getLabel(attribute.labels, user.catalogLocale, attribute.code)
    };
  });

  const attributesWithSuggestions = suggestedAttributes.concat(attributesWithoutSuggestions);

  const select2Configuration = useMemo(
    () => ({
      placeholder: ' ',
      allowClear: true,
      dropdownCssClass: 'select2--annotedLabels AknFranklin--attribute-selector',
      formatResult: createFormatResultCallback(
        attributes,
        attributeGroups,
        user.catalogLocale,
        suggestedAttributeCodes
      ),
      data: attributesWithSuggestions
    }),
    [attributes, attributeGroups, user.catalogLocale]
  );

  let attributeMappingStateCssClass = '';

  switch (attributesMappingStatus[franklinAttributeCode]) {
    case FrontAttributeMappingStatus.MAPPED:
      attributeMappingStateCssClass = 'perfect-match';
      break;
    case FrontAttributeMappingStatus.SUGGESTION_APPLIED:
      attributeMappingStateCssClass = 'suggestion';
      break;
  }

  let suggestedLabel = <div></div>;
  if (attributesMappingStatus[franklinAttributeCode] === FrontAttributeMappingStatus.SUGGESTION_APPLIED) {
    suggestedLabel = (
      <div className={'suggested-attribute'}>
        <Translate id='akeneo_franklin_insights.entity.attributes_mapping.module.index.suggested' />
      </div>
    );
  }

  return (
    <div>
      <div
        className={
          'AknFieldContainer-inputContainer ' + attributeMappingStateCssClass + (true === hasError ? ' error' : '')
        }
      >
        <Select2 configuration={select2Configuration} value={selectedAttributeCode} onChange={onSelect} />
      </div>
      {suggestedLabel}
    </div>
  );
};

function createFormatResultCallback(
  attributes: {[attributeCode: string]: Attribute},
  attributeGroups: {[attributeGroupCode: string]: AttributeGroup},
  locale: string,
  suggestedAttributeCodes: string[]
) {
  return ({id, text}: {id: string; text: string}) => {
    let attributeGroupLabel = '';
    let isSuggestedCssClass = '';

    const attributeGroup = attributes[id] && attributeGroups[attributes[id].group];
    if (attributeGroup) {
      attributeGroupLabel = `
        <span class="group-label">
          ${getLabel(attributeGroup.labels, locale, attributeGroup.code)}
        </span>`;
    }

    if (suggestedAttributeCodes.includes(id)) {
      isSuggestedCssClass = 'select2-result-label-attribute--suggested';
    }

    return `
      <div class="select2-result-label-attribute ${isSuggestedCssClass}">
        <span class="attribute-label">${text}</span>
        ${attributeGroupLabel}
      </div>`;
  };
}
