import React from 'react';
import {filterErrors, formatParameters, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {BooleanInput, Field, Helper} from 'akeneo-design-system';

type BooleanValueGeneratorConfiguratorProps = {
  value: boolean;
  validationErrors: ValidationError[];
  onValueChange: (value: boolean) => void;
};

const BooleanValueGeneratorConfigurator = ({
  value = false,
  validationErrors,
  onValueChange,
}: BooleanValueGeneratorConfiguratorProps) => {
  const translate = useTranslate();
  const errors = filterErrors(validationErrors, '[value]');

  return (
    <Field
      label={translate('akeneo.syndication.data_mapping_details.sources.static.boolean.generator.label')}
      incomplete={false}
    >
      <BooleanInput
        invalid={0 < errors.length}
        value={value}
        readOnly={false}
        yesLabel={translate('pim_common.yes')}
        noLabel={translate('pim_common.no')}
        onChange={onValueChange}
      />
      {formatParameters(errors).map((error, key) => (
        <Helper key={key} level="error" inline={true}>
          {translate(error.messageTemplate, error.parameters, error.plural)}
        </Helper>
      ))}
    </Field>
  );
};

export {BooleanValueGeneratorConfigurator};
