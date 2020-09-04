import { Controller } from 'react-hook-form';
import React from 'react';
import { ConditionLineProps } from './ConditionLineProps';
import {
  MeasurementAttributeCondition,
  MeasurementAttributeOperators,
} from '../../../../models/conditions';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { useControlledFormInputCondition } from '../../hooks';
import { Attribute } from '../../../../models';
import { AttributeConditionLine } from './AttributeConditionLine';
import { Operator } from '../../../../models/Operator';
import { useGetAttributeAtMount } from '../actions/attribute/attribute.utils';
import {
  InputMeasurement,
  isMeasurementAmountFilled,
  isMeasurementUnitFilled,
  MeasurementData,
} from '../../../../components/Inputs';

type MeasurementAttributeConditionLineProps = ConditionLineProps & {
  condition: MeasurementAttributeCondition;
};

const MeasurementAttributeConditionLine: React.FC<MeasurementAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();

  const {
    valueFormName,
    getValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputCondition<MeasurementData>(lineNumber);

  const [attribute, setAttribute] = React.useState<Attribute | null>();
  useGetAttributeAtMount(condition.field, router, attribute, setAttribute);

  return (
    <AttributeConditionLine
      attribute={attribute}
      availableOperators={MeasurementAttributeOperators}
      currentCatalogLocale={currentCatalogLocale}
      defaultOperator={Operator.IS_EMPTY}
      field={condition.field}
      lineNumber={lineNumber}
      locales={locales}
      scopes={scopes}
      valueHasError={isFormFieldInError('value')}>
      {attribute && (
        <Controller
          as={InputMeasurement}
          name={valueFormName}
          id={`edit-rules-input-${lineNumber}-value`}
          defaultValue={getValueFormValue()}
          value={getValueFormValue()}
          attribute={attribute}
          hiddenLabel
          hasError={isFormFieldInError('value')}
          rules={{
            required: translate('pimee_catalog_rule.exceptions.required'),
            validate: (value: any) => {
              if (!isMeasurementAmountFilled(value)) {
                return translate('pimee_catalog_rule.exceptions.required');
              } else if (!isMeasurementUnitFilled(value)) {
                return translate('pimee_catalog_rule.exceptions.required_unit');
              }

              return true;
            },
          }}
        />
      )}
    </AttributeConditionLine>
  );
};

export {
  MeasurementAttributeConditionLine,
  MeasurementAttributeConditionLineProps,
};
