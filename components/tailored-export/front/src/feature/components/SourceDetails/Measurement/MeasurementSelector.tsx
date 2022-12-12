import React, {useState} from 'react';
import {Collapse, Field, Helper, Pill, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  Section,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels} from '../../../hooks';
import {LocaleDropdown} from '../../LocaleDropdown';
import {DecimalSeparatorDropdown} from '../../DecimalSeparatorDropdown';
import {isDefaultMeasurementSelection, MeasurementSelection} from './model';

type MeasurementSelectorProps = {
  selection: MeasurementSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: MeasurementSelection) => void;
};

const MeasurementSelector = ({selection, validationErrors, onSelectionChange}: MeasurementSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const typeErrors = filterErrors(validationErrors, '[type]');
  const decimalSeparatorErrors = filterErrors(validationErrors, '[decimal_separator]');

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.tailored_export.column_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultMeasurementSelection(selection) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
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
              switch (type) {
                case 'unit_code':
                case 'unit_symbol':
                  onSelectionChange({type});
                  break;
                case 'unit_label':
                  onSelectionChange({type, locale: locales[0].code});
                  break;
                case 'value':
                  onSelectionChange({type, decimal_separator: '.'});
                  break;
                case 'value_and_unit_label':
                  onSelectionChange({type, decimal_separator: '.', locale: locales[0].code});
                  break;
                case 'value_and_unit_symbol':
                  onSelectionChange({type, decimal_separator: '.'});
                  break;
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
              title={translate('akeneo.tailored_export.column_details.sources.selection.measurement.unit_symbol')}
              value="unit_symbol"
            >
              {translate('akeneo.tailored_export.column_details.sources.selection.measurement.unit_symbol')}
            </SelectInput.Option>
            <SelectInput.Option
              title={translate('akeneo.tailored_export.column_details.sources.selection.measurement.value')}
              value="value"
            >
              {translate('akeneo.tailored_export.column_details.sources.selection.measurement.value')}
            </SelectInput.Option>
            <SelectInput.Option
              title={translate(
                'akeneo.tailored_export.column_details.sources.selection.measurement.value_and_unit_label'
              )}
              value="value_and_unit_label"
            >
              {translate('akeneo.tailored_export.column_details.sources.selection.measurement.value_and_unit_label')}
            </SelectInput.Option>
            <SelectInput.Option
              title={translate(
                'akeneo.tailored_export.column_details.sources.selection.measurement.value_and_unit_symbol'
              )}
              value="value_and_unit_symbol"
            >
              {translate('akeneo.tailored_export.column_details.sources.selection.measurement.value_and_unit_symbol')}
            </SelectInput.Option>
          </SelectInput>
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
        {'value' === selection.type && (
          <DecimalSeparatorDropdown
            label={translate('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title')}
            value={selection.decimal_separator ?? '.'}
            validationErrors={decimalSeparatorErrors}
            onChange={updatedValue => onSelectionChange({...selection, decimal_separator: updatedValue})}
          />
        )}
        {'value_and_unit_label' === selection.type && (
          <>
            <DecimalSeparatorDropdown
              label={translate('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title')}
              value={selection.decimal_separator}
              validationErrors={decimalSeparatorErrors}
              onChange={updatedValue => onSelectionChange({...selection, decimal_separator: updatedValue})}
            />
            <LocaleDropdown
              label={translate('akeneo.tailored_export.column_details.sources.selection.measurement.unit_locale')}
              value={selection.locale}
              validationErrors={localeErrors}
              locales={locales}
              onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
            />
          </>
        )}
        {'value_and_unit_symbol' === selection.type && (
          <DecimalSeparatorDropdown
            label={translate('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title')}
            value={selection.decimal_separator}
            validationErrors={decimalSeparatorErrors}
            onChange={updatedValue => onSelectionChange({...selection, decimal_separator: updatedValue})}
          />
        )}
      </Section>
    </Collapse>
  );
};

export {MeasurementSelector};
