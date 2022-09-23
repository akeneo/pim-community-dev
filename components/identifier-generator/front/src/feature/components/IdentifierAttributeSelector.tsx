import React from 'react';
import {SelectInput} from 'akeneo-design-system';
import {useIdentifierAttributes} from '../hooks/useIdentifierAttributes';
import {useTranslate} from '@akeneo-pim-community/shared';

const IdentifierAttributeSelector: React.FC<{code: string}> = ({code}) => {
  const translate = useTranslate();
  const {data: attributes} = useIdentifierAttributes();

  return (
    <SelectInput
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      placeholder="Editable select"
      readOnly={true}
      value={code}
    >
      {attributes?.map(attribute => (
        <SelectInput.Option key={attribute.code} title={attribute?.label} value={attribute.code}>
          {attribute?.label}
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {IdentifierAttributeSelector};
