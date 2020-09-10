import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import { FallbackField } from '../FallbackField';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { useControlledFormInputCondition } from '../../hooks';

const PimConditionLine: React.FC<ConditionLineProps> = ({ lineNumber }) => {
  const translate = useTranslate();
  const { watch } = useFormContext();
  const {
    formName,
    getFormValue,
    getOperatorFormValue,
    getValueFormValue,
    getFieldFormValue,
    getLocaleFormValue,
    getScopeFormValue,
  } = useControlledFormInputCondition<string[]>(lineNumber);

  const getConditionValues = () => watch(`content.conditions[${lineNumber}]`);

  const isMetric = (value: any): boolean => {
    return (
      value !== null &&
      typeof value === 'object' &&
      typeof value.amount !== 'undefined' &&
      typeof value.unit !== 'undefined'
    );
  };

  const isPrice = (value: any): boolean => {
    return (
      value !== null &&
      typeof value === 'object' &&
      typeof value.amount !== 'undefined' &&
      typeof value.currency !== 'undefined'
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

  return (
    <div className='AknGrid-bodyCell AknRule'>
      <span className='AknRule-attribute'>
        {Object.keys(getConditionValues() ?? {}).map((key: string) => (
          <Controller
            as={<span hidden />}
            name={formName(key)}
            defaultValue={getFormValue(key)}
            key={key}
          />
        ))}
        <FallbackField
          field={getFieldFormValue()}
          scope={getScopeFormValue()}
          locale={getLocaleFormValue()}
        />
      </span>
      &nbsp;
      {translate(
        `pimee_catalog_rule.form.edit.conditions.operators.${getOperatorFormValue()}`
      )}
      &nbsp;
      <span className='AknRule-attribute'>
        {displayValue(getValueFormValue())}
      </span>
    </div>
  );
};

export { PimConditionLine };
