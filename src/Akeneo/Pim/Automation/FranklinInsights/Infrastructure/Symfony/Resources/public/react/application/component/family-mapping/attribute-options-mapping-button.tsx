/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {useContext} from 'react';

import {TranslateContext} from '../../context/translate-context';
import {createAttributeOptionMappingModal} from '../../../infrastructure/modal/attribute-option-mapping';
import {UserContext} from '../../context/user-context';
import {useSelector} from 'react-redux';
import {FamilyMappingState} from '../../reducer/family-mapping';
import {getFamilyLabel} from '../../get-family-label';

interface Props {
  familyCode: string;
  attributeCode: string;
  franklinAttributeCode: string;
}

export const AttributeOptionsMappingButton = ({familyCode, attributeCode, franklinAttributeCode}: Props) => {
  const translate = useContext(TranslateContext);

  const user = useContext(UserContext);
  const familyLabel: string = useSelector((state: FamilyMappingState) => {
    return getFamilyLabel(state.family, familyCode, user.catalogLocale);
  });

  const franklinAttributeLabel: string = useSelector((state: FamilyMappingState) => {
    return state.familyMapping.mapping[franklinAttributeCode].franklinAttribute.label;
  });

  const handleAttributeOptionMapping = () =>
    createAttributeOptionMappingModal(
      familyCode,
      familyLabel,
      attributeCode,
      franklinAttributeCode,
      franklinAttributeLabel
    );

  return (
    <div className='AknFieldContainer-iconsContainer icons-container'>
      <div
        className='AknIconButton AknIconButton--small AknIconButton--edit AknGrid-onHoverElement option-mapping'
        title={translate('pim_common.edit')}
        onClick={handleAttributeOptionMapping}
        data-franklin-attribute-code={franklinAttributeCode}
      />
    </div>
  );
};
