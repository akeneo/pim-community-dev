import React, {useState} from 'react';
import {
  filterErrors,
  getLabel,
  Section,
  useTranslate,
  useUserContext,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {Collapse, Field, Helper, Pill, SelectInput} from 'akeneo-design-system';
import {useMeasurementFamily} from '../../../hooks';
import {
  getDefaultMeasurementConversionOperation,
  MeasurementConversionOperation,
  isDefaultMeasurementConversionOperation,
} from './model';

type MeasurementConversionProps = {
  operation?: MeasurementConversionOperation;
  measurementFamilyCode: string;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation?: MeasurementConversionOperation) => void;
};

const MeasurementConversion = ({
  operation = getDefaultMeasurementConversionOperation(),
  measurementFamilyCode,
  validationErrors,
  onOperationChange,
}: MeasurementConversionProps) => {
  const translate = useTranslate();
  const [isMeasurementConversionCollapsed, toggleMeasurementConversionCollapse] = useState<boolean>(false);
  const measurementFamily = useMeasurementFamily(measurementFamilyCode);
  const catalogLocale = useUserContext().get('catalogLocale');
  const targetUnitCodeErrors = filterErrors(validationErrors, '[target_unit_code]');

  return (
    <Collapse
      collapseButtonLabel={
        isMeasurementConversionCollapsed ? translate('pim_common.close') : translate('pim_common.open')
      }
      label={
        <>
          {translate('akeneo.syndication.data_mapping_details.sources.operation.measurement_conversion.title')}
          {0 === validationErrors.length && !isDefaultMeasurementConversionOperation(operation) && (
            <Pill level="primary" />
          )}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isMeasurementConversionCollapsed}
      onCollapse={toggleMeasurementConversionCollapse}
    >
      <Section>
        <Field
          label={translate(
            'akeneo.syndication.data_mapping_details.sources.operation.measurement_conversion.target_unit_code.label'
          )}
        >
          {null !== measurementFamily && (
            <SelectInput
              clearable={true}
              invalid={0 < targetUnitCodeErrors.length}
              emptyResultLabel={translate('pim_common.no_result')}
              clearLabel={translate('pim_common.clear_value')}
              placeholder={translate(
                'akeneo.syndication.data_mapping_details.sources.operation.measurement_conversion.placeholder'
              )}
              openLabel={translate('pim_common.open')}
              value={operation.target_unit_code}
              onChange={(targetUnitCode: string | null) => {
                const newOperation = {...operation, target_unit_code: targetUnitCode};

                onOperationChange(isDefaultMeasurementConversionOperation(newOperation) ? undefined : newOperation);
              }}
            >
              {measurementFamily.units.map(({code, labels}) => (
                <SelectInput.Option key={code} title={getLabel(labels, catalogLocale, code)} value={code}>
                  {getLabel(labels, catalogLocale, code)}
                </SelectInput.Option>
              ))}
            </SelectInput>
          )}
          {targetUnitCodeErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
      </Section>
    </Collapse>
  );
};

export {MeasurementConversion};
export type {MeasurementConversionOperation};
