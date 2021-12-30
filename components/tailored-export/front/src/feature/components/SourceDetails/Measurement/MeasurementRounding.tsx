import React, {useState} from 'react';
import {filterErrors, Section, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {BooleanInput, Collapse, Field, Helper, NumberInput, Pill} from 'akeneo-design-system';
import {
  DEFAULT_PRECISION,
  getDefaultMeasurementRoundingOperation,
  isDefaultMeasurementRoundingOperation,
  MeasurementRoundingOperation,
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
        <Field label={translate('akeneo.tailored_export.column_details.sources.operation.measurement_rounding.label')}>
          <BooleanInput
            value={operation.rounding_type === 'standard'}
            onChange={(shouldRound: boolean) => {
              // TODO: I'm sure team will have a more elegant way of doing this
              if (!shouldRound) {
                onOperationChange(undefined);
                return;
              } else {
                const newOperation = {
                  ...operation,
                  rounding_type: 'standard' as RoundingType,
                  precision: DEFAULT_PRECISION,
                };
                onOperationChange(newOperation);
              }
            }}
            noLabel={translate('pim_common.no')}
            yesLabel={translate('pim_common.yes')}
            readOnly={false}
          />
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
              min={0}
              max={12}
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
