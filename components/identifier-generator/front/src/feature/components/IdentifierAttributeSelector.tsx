import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useIdentifierAttributes} from '../hooks/';
import {useTranslate} from '@akeneo-pim-community/shared';

enum Status {
  FORBIDDEN,
  ERROR,
  SUCCESS,
}

const IdentifierAttributeSelector: React.FC<{code: string}> = ({code}) => {
  const translate = useTranslate();
  const {data: attributes = [], error} = useIdentifierAttributes();
  const status = error?.message === 'Forbidden' ? Status.FORBIDDEN : error?.message ? Status.ERROR : Status.SUCCESS;

  return (
    <Field label={translate('pim_identifier_generator.create.form.select_identifier_attribute')}>
      <SelectInput
        emptyResultLabel={translate('pim_common.no_result')}
        data-testid="identifierAttribute"
        openLabel={translate('pim_common.open')}
        placeholder=""
        readOnly={true}
        value={status === Status.SUCCESS ? code : null}
      >
        {attributes?.map(attribute => (
          <SelectInput.Option key={attribute.code} title={attribute?.label} value={attribute.code}>
            {attribute?.label}
          </SelectInput.Option>
        ))}
      </SelectInput>
      {status === Status.FORBIDDEN && (
        <Helper inline level="error">
          {translate('pim_error.unauthorized')}
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
