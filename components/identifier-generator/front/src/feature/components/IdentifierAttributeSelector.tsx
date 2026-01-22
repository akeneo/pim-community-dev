import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useIdentifierAttributes} from '../hooks/';
import {useTranslate} from '@akeneo-pim-community/shared';
import {AttributeCode} from '@akeneo-pim-community/structure';

enum Status {
  FORBIDDEN,
  ERROR,
  SUCCESS,
}

type IdentifierAttributeSelector = {
  code: AttributeCode;
  onChange: (attributeCode: AttributeCode) => void;
};

const IdentifierAttributeSelector: React.FC<IdentifierAttributeSelector> = ({code, onChange}) => {
  const translate = useTranslate();
  const {data: attributes = [], error} = useIdentifierAttributes();
  const status = error?.message === 'Forbidden' ? Status.FORBIDDEN : error?.message ? Status.ERROR : Status.SUCCESS;
  const handleChange = (attributeCode: AttributeCode) => {
    onChange(attributeCode);
  };

  return (
    <Field label={translate('pim_identifier_generator.create.form.select_identifier_attribute')}>
      <SelectInput
        emptyResultLabel={translate('pim_common.no_result')}
        data-testid="identifierAttribute"
        openLabel={translate('pim_common.open')}
        placeholder=""
        value={status === Status.SUCCESS ? code : null}
        onChange={handleChange}
        clearable={false}
      >
        {attributes?.map(attribute => (
          <SelectInput.Option key={attribute.code} title={attribute?.label} value={attribute.code}>
            {attribute?.label}
          </SelectInput.Option>
        ))}
      </SelectInput>
      {status === Status.FORBIDDEN && (
        <Helper inline level="error">
          {translate('pim_error.unauthorized_list_attributes')}
        </Helper>
      )}
      {status === Status.ERROR && (
        <Helper inline level="error">
          {translate('pim_error.general')}
        </Helper>
      )}
    </Field>
  );
};

export {IdentifierAttributeSelector};
