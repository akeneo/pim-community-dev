import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  Section,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {CodeLabelSelection} from '../../../models';
import {useChannels} from '../../../hooks';
import {LocaleDropdown} from '../../LocaleDropdown';

type CodeLabelSelectorProps = {
  selection: CodeLabelSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: CodeLabelSelection) => void;
};

const CodeLabelSelector = ({selection, validationErrors, onSelectionChange}: CodeLabelSelectorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const localeErrors = filterErrors(validationErrors, '[locale]');
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
            if ('label' === type) {
              onSelectionChange({type, locale: locales[0].code});
            } else if ('code' === type) {
              onSelectionChange({type});
            }
          }}
        >
          <SelectInput.Option title={translate('pim_common.label')} value="label">
            {translate('pim_common.label')}
          </SelectInput.Option>
          <SelectInput.Option title={translate('pim_common.code')} value="code">
            {translate('pim_common.code')}
          </SelectInput.Option>
        </SelectInput>
        {typeErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
      {'label' === selection.type && (
        <LocaleDropdown
          value={selection.locale}
          validationErrors={localeErrors}
          locales={locales}
          onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
        />
      )}
    </Section>
  );
};

export {CodeLabelSelector};
