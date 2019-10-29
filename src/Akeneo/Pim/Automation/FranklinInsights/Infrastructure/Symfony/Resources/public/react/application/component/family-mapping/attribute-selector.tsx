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

interface Props {
  selectedAttributeCode?: string;
  hasError?: boolean;
  onSelect: (attributeCode: string) => void;
}

export const AttributeSelector = ({selectedAttributeCode, hasError, onSelect}: Props) => {
  const user = useContext(UserContext);

  const attributes = useSelector((state: FamilyMappingState) => state.attributes);
  const attributeGroups = useSelector((state: FamilyMappingState) => state.attributeGroups);

  const select2Configuration = useMemo(
    () => ({
      placeholder: ' ',
      allowClear: true,
      dropdownCssClass: 'select2--annotedLabels',
      formatResult: createFormatResultCallback(attributes, attributeGroups, user.catalogLocale),
      data: Object.values(attributes).map(attribute => ({
        id: attribute.code,
        text: getLabel(attribute.labels, user.catalogLocale, attribute.code)
      }))
    }),
    [attributes, attributeGroups, user.catalogLocale]
  );

  return (
    <div
      className={
        'AknFieldContainer-inputContainer' +
        (undefined !== selectedAttributeCode ? ' perfect-match' : '') +
        (true === hasError ? ' error' : '')
      }
    >
      <Select2 configuration={select2Configuration} value={selectedAttributeCode} onChange={onSelect} />
    </div>
  );
};

function createFormatResultCallback(
  attributes: {[attributeCode: string]: Attribute},
  attributeGroups: {[attributeGroupCode: string]: AttributeGroup},
  locale: string
) {
  return ({id, text}: {id: string; text: string}) => {
    let attributeGroupLabel = '';

    const attributeGroup = attributes[id] && attributeGroups[attributes[id].group];
    if (attributeGroup) {
      attributeGroupLabel = `
        <span class="group-label">
          ${getLabel(attributeGroup.labels, locale, attributeGroup.code)}
        </span>`;
    }

    return `
      <div class="select2-result-label-attribute">
        <span class="attribute-label">${text}</span>
        ${attributeGroupLabel}
      </div>`;
  };
}
