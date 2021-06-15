import React from 'react';
import {Field, SelectInput} from 'akeneo-design-system';
import {getAllLocalesFromChannels, Section, useTranslate} from '@akeneo-pim-community/shared';
import {MeasurementSelection} from '../../../../models';
import {useChannels} from '../../../../hooks';
import {LocaleDropdown} from '../LocaleDropdown';

type MeasurementSelectorProps = {
  selection: MeasurementSelection;
  onSelectionChange: (updatedSelection: MeasurementSelection) => void;
};

const MeasurementSelector = ({selection, onSelectionChange}: MeasurementSelectorProps) => {
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
      </Field>
      {'label' === selection.type && (
        <LocaleDropdown
          value={selection.locale}
          onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
        />
      )}
    </Section>
  );
};

export {MeasurementSelector};
