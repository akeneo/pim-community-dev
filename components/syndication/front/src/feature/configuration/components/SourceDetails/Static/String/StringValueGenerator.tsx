import React from 'react';
import {filterErrors, formatParameters, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Field, Helper, TextInput} from 'akeneo-design-system';

type StringValueGeneratorConfiguratorProps = {
  value: string;
  validationErrors: ValidationError[];
  onValueChange: (value: string) => void;
};

const StringValueGeneratorConfigurator = ({
  value = '',
  validationErrors,
  onValueChange,
}: StringValueGeneratorConfiguratorProps) => {
  const translate = useTranslate();
  const errors = filterErrors(validationErrors, '[value]');

  return (
    <Field
      label={translate('akeneo.syndication.data_mapping_details.sources.static.string.generator.label')}
      incomplete={false}
    >
      <TextInput invalid={0 < errors.length} value={value} readOnly={false} onChange={onValueChange} />
      {formatParameters(errors).map((error, key) => (
        <Helper key={key} level="error" inline={true}>
          {translate(error.messageTemplate, error.parameters, error.plural)}
        </Helper>
      ))}
    </Field>
  );
};

export {StringValueGeneratorConfigurator};
