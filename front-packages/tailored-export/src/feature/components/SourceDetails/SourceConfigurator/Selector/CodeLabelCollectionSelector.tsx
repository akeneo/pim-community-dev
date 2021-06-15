import React from 'react';
import {Field, SelectInput} from 'akeneo-design-system';
import {getAllLocalesFromChannels, Section, useTranslate} from '@akeneo-pim-community/shared';
import {availableSeparators, isSelectionSeparator, CodeLabelCollectionSelection} from '../../../../models';
import {useChannels} from '../../../../hooks';
import {LocaleDropdown} from '../LocaleDropdown';

type CodeLabelCollectionSelectorProps = {
  selection: CodeLabelCollectionSelection;
  onSelectionChange: (updatedSelection: CodeLabelCollectionSelection) => void;
};

const CodeLabelCollectionSelector = ({selection, onSelectionChange}: CodeLabelCollectionSelectorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);

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
              onSelectionChange({...selection, type, locale: locales[0].code});
            } else if ('code' === type) {
              onSelectionChange({...selection, type});
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
          onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
        />
      )}
      <Field label={translate('akeneo.tailored_export.column_details.sources.selection.separator')}>
        <SelectInput
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.separator}
          onChange={separator => {
            if (isSelectionSeparator(separator)) {
              onSelectionChange({...selection, separator});
            }
          }}
        >
          {availableSeparators.map((availableSeparator) => (
            <SelectInput.Option key={availableSeparator} title={availableSeparator} value={availableSeparator}>
              {availableSeparator}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </Field>
    </Section>
  );
};

export {CodeLabelCollectionSelector};
