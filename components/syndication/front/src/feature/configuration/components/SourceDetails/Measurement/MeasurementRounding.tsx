import React, {useState} from 'react';
import {filterErrors, Section, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Collapse, Field, Helper, NumberInput, Pill, SelectInput} from 'akeneo-design-system';
import {
  availableRoundingTypes,
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
          {translate('akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.title')}
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
            'akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.rounding_type.label'
          )}
        >
          <SelectInput
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
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
            {availableRoundingTypes.map(roundingType => (
              <SelectInput.Option
                key={roundingType}
                title={translate(
                  `akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.rounding_type.types.${roundingType}`
                )}
                value={roundingType}
              >
                {translate(
                  `akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.rounding_type.types.${roundingType}`
                )}
              </SelectInput.Option>
            ))}
          </SelectInput>
        </Field>
        {'no_rounding' !== operation.rounding_type && (
          <Field
            label={translate(
              'akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.precision.label'
            )}
          >
            <NumberInput
              value={typeof operation.precision === 'undefined' ? '' : `${operation.precision}`}
              placeholder={translate(
                'akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.precision.placeholder'
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
