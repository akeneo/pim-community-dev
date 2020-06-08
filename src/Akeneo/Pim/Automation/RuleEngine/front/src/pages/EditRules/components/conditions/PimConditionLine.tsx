import React from 'react';
import { PimCondition } from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { FallbackField } from '../FallbackField';
import { useFormContext } from 'react-hook-form';
import { Operator } from '../../../../models/Operator';
import { useRegisterConsts } from '../../hooks/useRegisterConst';
import { useTranslate } from '../../../../dependenciesTools/hooks';

type PimConditionLineProps = ConditionLineProps & {
  condition: PimCondition;
};

const PimConditionLine: React.FC<PimConditionLineProps> = ({
  condition,
  lineNumber,
}) => {
  const translate = useTranslate();
  const { watch } = useFormContext();

  const values: { [key: string]: any } = {
    field: condition.field,
    operator: condition.operator,
  };
  if (condition.locale) {
    values.locale = condition.locale;
  }
  if (condition.scope) {
    values.scope = condition.scope;
  }
  if (condition.value) {
    values.value = condition.value;
  }
  useRegisterConsts(values, `content.conditions[${lineNumber}]`);

  const isMetric = (value: any): boolean => {
    return (
      typeof value === 'object' &&
      Object.hasOwnProperty.call(value, 'amount') &&
      Object.hasOwnProperty.call(value, 'unit')
    );
  };

  const isPrice = (value: any): boolean => {
    return (
      typeof value === 'object' &&
      Object.hasOwnProperty.call(value, 'amount') &&
      Object.hasOwnProperty.call(value, 'currency')
    );
  };

  const displayValue = (value: any): string => {
    if (null === value || 'undefined' === typeof value) {
      return '';
    }
    if (Array.isArray(value)) {
      return value
        .map(value => {
          return displayValue(value);
        })
        .join(', ');
    }
    if (typeof value === 'boolean') {
      return value ? translate('pim_common.yes') : translate('pim_common.no');
    }
    if (isMetric(value)) {
      return `${value.amount} ${value.unit}`;
    }
    if (isPrice(value)) {
      return `${value.amount} ${value.currency}`;
    }
    if (typeof value === 'object') {
      return JSON.stringify(value);
    }

    return value;
  };

  const getValueFormValue: () => any = () =>
    watch(`content.conditions[${lineNumber}].value`);
  const getOperatorFormValue: () => Operator = () =>
    watch(`content.conditions[${lineNumber}].operator`);

  return (
    <div className='AknGrid-bodyCell AknRule'>
      <span className='AknRule-attribute'>
        <FallbackField
          field={condition.field}
          scope={condition.scope}
          locale={condition.locale}
        />
      </span>
      {` ${translate(
        `pimee_catalog_rule.form.edit.conditions.operators.${getOperatorFormValue()}`
      )} `}
      <span className='AknRule-attribute'>
        {displayValue(getValueFormValue())}
      </span>
    </div>
  );
};

export { PimConditionLine, PimConditionLineProps };
