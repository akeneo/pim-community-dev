import React, {useState} from 'react';
import {filterErrors, Section, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Collapse, Field, Helper, NumberInput, Pill, SelectInput} from 'akeneo-design-system';
import {
  DEFAULT_PRECISION,
  getDefaultMeasurementRoundingOperation,
  isDefaultMeasurementRoundingOperation,
  MAX_PRECISION,
  MeasurementRoundingOperation,
  MIN_PRECISION,
  RoundingType,
} from './model';

type MeasurementRoundingProps = {
  operation?: MeasurementRoundingOperation;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation?: MeasurementRoundingOperation) => void;
};

const MeasurementRounding = ({
  operation = getDefaultMeasurementRoundingOperation(),
  validationErrors,
  onOperationChange,
}: MeasurementRoundingProps) => {
  const translate = useTranslate();
  const [isMeasurementRoundingCollapsed, toggleMeasurementRoundingCollapse] = useState<boolean>(false);
  const precisionErrors = filterErrors(validationErrors, '[precision]');

  return (
    <Collapse
      collapseButtonLabel={
        isMeasurementRoundingCollapsed ? translate('pim_common.close') : translate('pim_common.open')
      }
      label={
        <>
          {translate('akeneo.tailored_export.column_details.sources.operation.measurement_rounding.title')}
          {0 === validationErrors.length && !isDefaultMeasurementRoundingOperation(operation) && (
            <Pill level="primary" />
          )}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isMeasurementRoundingCollapsed}
      onCollapse={toggleMeasurementRoundingCollapse}
    >
      <Section>
        <Field
          label={translate(
            'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.rounding_type.label'
          )}
        >
          <SelectInput
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            placeholder={translate(
              'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.rounding_type.placeholder'
            )}
            openLabel={translate('pim_common.open')}
            value={operation?.rounding_type ?? 'no_rounding'}
            onChange={(roundingType: string | null) => {
              if (null === roundingType || 'no_rounding' === roundingType) {
                onOperationChange(undefined);
              } else {
                const newOperation = {
                  ...operation,
                  rounding_type: roundingType as RoundingType,
                  precision: DEFAULT_PRECISION,
                };
                onOperationChange(newOperation);
              }
            }}
          >
            <SelectInput.Option
              key={'no_rounding'}
              title={translate(
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.rounding_type.types.no_rounding'
              )}
              value={'no_rounding'}
            >
              {translate(
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.rounding_type.types.no_rounding'
              )}
            </SelectInput.Option>
            <SelectInput.Option
              key={'round'}
              title={translate(
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.rounding_type.types.standard'
              )}
              value={'standard'}
            >
              {translate(
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.rounding_type.types.standard'
              )}
            </SelectInput.Option>
            <SelectInput.Option
              key={'round_up'}
              title={translate(
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.rounding_type.types.round_up'
              )}
              value={'round_up'}
            >
              {translate(
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.rounding_type.types.round_up'
              )}
            </SelectInput.Option>
            <SelectInput.Option
              key={'round_down'}
              title={translate(
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.rounding_type.types.round_down'
              )}
              value={'round_down'}
            >
              {translate(
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.rounding_type.types.round_down'
              )}
            </SelectInput.Option>
          </SelectInput>
        </Field>
        {'no_rounding' !== operation.rounding_type && (
          <Field
            label={translate(
              'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.precision.label'
            )}
          >
            <NumberInput
              value={typeof operation.precision === 'undefined' ? '' : `${operation.precision}`}
              placeholder={translate(
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.precision.placeholder'
              )}
              onChange={value => {
                const newOperation = {...operation, precision: parseInt(value)};
                onOperationChange(newOperation);
              }}
              min={MIN_PRECISION}
              max={MAX_PRECISION}
            />
            {precisionErrors.map((error, index) => (
              <Helper key={index} inline={true} level="error">
                {translate(error.messageTemplate, error.parameters)}
              </Helper>
            ))}
          </Field>
        )}
      </Section>
    </Collapse>
  );
};

export {MeasurementRounding};
export type {MeasurementRoundingOperation};
