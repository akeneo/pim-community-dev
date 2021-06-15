import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  Section,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {MeasurementSelection} from '../../../../models';
import {useChannels} from '../../../../hooks';
import {LocaleDropdown} from '../LocaleDropdown';

type MeasurementSelectorProps = {
  selection: MeasurementSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: MeasurementSelection) => void;
};

const MeasurementSelector = ({selection, validationErrors, onSelectionChange}: MeasurementSelectorProps) => {
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
            } else if ('code' === type || 'amount' === type) {
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
          <SelectInput.Option
            title={translate('akeneo.tailored_export.column_details.sources.selection.type.amount')}
            value="amount"
          >
            {translate('akeneo.tailored_export.column_details.sources.selection.type.amount')}
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
          onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
        />
      )}
    </Section>
  );
};

export {MeasurementSelector};
