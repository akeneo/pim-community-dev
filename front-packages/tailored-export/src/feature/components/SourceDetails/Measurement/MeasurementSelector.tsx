import React, {useState} from 'react';
import {Collapse, Field, Helper, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  Section,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels} from '../../../hooks';
import {LocaleDropdown} from '../../LocaleDropdown';
import {MeasurementSelection} from './model';

type MeasurementSelectorProps = {
  selection: MeasurementSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: MeasurementSelection) => void;
};

const MeasurementSelector = ({selection, validationErrors, onSelectionChange}: MeasurementSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const typeErrors = filterErrors(validationErrors, '[type]');

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Section>
        <Field label={translate('pim_common.type')}>
          <SelectInput
            clearable={false}
            invalid={0 < typeErrors.length}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={selection.type}
            onChange={type => {
              if ('unit_label' === type) {
                onSelectionChange({type, locale: locales[0].code});
              } else if ('unit_code' === type || 'value' === type) {
                onSelectionChange({type});
              }
            }}
          >
            <SelectInput.Option
              title={translate('akeneo.tailored_export.column_details.sources.selection.measurement.unit_label')}
              value="unit_label"
            >
              {translate('akeneo.tailored_export.column_details.sources.selection.measurement.unit_label')}
            </SelectInput.Option>
            <SelectInput.Option
              title={translate('akeneo.tailored_export.column_details.sources.selection.measurement.unit_code')}
              value="unit_code"
            >
              {translate('akeneo.tailored_export.column_details.sources.selection.measurement.unit_code')}
            </SelectInput.Option>
            <SelectInput.Option
              title={translate('akeneo.tailored_export.column_details.sources.selection.measurement.value')}
              value="value"
            >
              {translate('akeneo.tailored_export.column_details.sources.selection.measurement.value')}
            </SelectInput.Option>
          </SelectInput>
          <Helper inline={true} level="info">
            {translate('akeneo.tailored_export.column_details.sources.selection.measurement.information')}
          </Helper>
          {typeErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
        {'unit_label' === selection.type && (
          <LocaleDropdown
            label={translate('akeneo.tailored_export.column_details.sources.selection.measurement.unit_locale')}
            value={selection.locale}
            validationErrors={localeErrors}
            locales={locales}
            onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
          />
        )}
      </Section>
    </Collapse>
  );
};

export {MeasurementSelector};
