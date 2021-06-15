import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  Section,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {availableSeparators, isCollectionSeparator, CodeLabelCollectionSelection} from '../../../../models';
import {useChannels} from '../../../../hooks';
import {LocaleDropdown} from '../LocaleDropdown';

type CodeLabelCollectionSelectorProps = {
  selection: CodeLabelCollectionSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: CodeLabelCollectionSelection) => void;
};

const CodeLabelCollectionSelector = ({
  selection,
  validationErrors,
  onSelectionChange,
}: CodeLabelCollectionSelectorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const separatorErrors = filterErrors(validationErrors, '[separator]');
  const localeErrors = filterErrors(validationErrors, '[locale]');

  return (
    <Section>
      <Field label={translate('pim_common.type')}>
        <SelectInput
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.type}
          onChange={type => {
            if ('label' === type) {
              onSelectionChange({type, locale: locales[0].code, separator: selection.separator});
            } else if ('code' === type) {
              onSelectionChange({type, separator: selection.separator});
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
      </Field>
      {'label' === selection.type && (
        <LocaleDropdown
          value={selection.locale}
          validationErrors={localeErrors}
          onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
        />
      )}
      <Field label={translate('akeneo.tailored_export.column_details.sources.selection.separator')}>
        <SelectInput
          invalid={0 < separatorErrors.length}
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.separator}
          onChange={separator => {
            if (isCollectionSeparator(separator)) {
              onSelectionChange({...selection, separator});
            }
          }}
        >
          {availableSeparators.map(availableSeparator => (
            <SelectInput.Option key={availableSeparator} title={availableSeparator} value={availableSeparator}>
              {availableSeparator}
            </SelectInput.Option>
          ))}
        </SelectInput>
        {separatorErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
    </Section>
  );
};

export {CodeLabelCollectionSelector};
