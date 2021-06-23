import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {Section, filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {FileSelection} from '../../../../models';

type FileSelectorProps = {
  selection: FileSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: FileSelection) => void;
};

const FileSelector = ({selection, validationErrors, onSelectionChange}: FileSelectorProps) => {
  const translate = useTranslate();
  const typeErrors = filterErrors(validationErrors, '[type]');

  return (
    <Section>
      <Field label={translate('pim_common.type')}>
        <SelectInput
          clearable={false}
          invalid={0 < typeErrors.length}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.type}
          onChange={type => {
            if ('path' === type || 'key' === type || 'name' === type) {
              onSelectionChange({type});
            }
          }}
        >
          <SelectInput.Option
            title={translate('akeneo.tailored_export.column_details.sources.selection.type.path')}
            value="path"
          >
            {translate('akeneo.tailored_export.column_details.sources.selection.type.path')}
          </SelectInput.Option>
          <SelectInput.Option
            title={translate('akeneo.tailored_export.column_details.sources.selection.type.key')}
            value="key"
          >
            {translate('akeneo.tailored_export.column_details.sources.selection.type.key')}
          </SelectInput.Option>
          <SelectInput.Option
            title={translate('akeneo.tailored_export.column_details.sources.selection.type.name')}
            value="name"
          >
            {translate('akeneo.tailored_export.column_details.sources.selection.type.name')}
          </SelectInput.Option>
        </SelectInput>
        {typeErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
    </Section>
  );
};

export {FileSelector};
