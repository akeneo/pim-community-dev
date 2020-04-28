import React, { ReactElement } from 'react';
import { useFormContext } from 'react-hook-form';
import { PimCondition } from '../../models/PimCondition';
import { Flag } from '../../components/Flag/Flag';
import { ConditionLineProps } from './ConditionLineProps';

const PimConditionLine: React.FC<ConditionLineProps> = ({
  translate,
  condition,
  lineNumber,
}) => {
  const pimCondition = condition as PimCondition;
  const { register, getValues, setValue } = useFormContext();

  const initializeValue = (field: string, value: any): void => {
    const key = `content.conditions[${lineNumber}].${field}`;
    register(key);
    if (undefined === getValues()[key]) {
      setValue(key, value);
    }
  }
  initializeValue('field', pimCondition.field);
  initializeValue('operator', pimCondition.operator);
  if (pimCondition.locale) {
    initializeValue('locale', pimCondition.locale);
  }
  if (pimCondition.scope) {
    initializeValue('scope', pimCondition.scope);
  }
  if (pimCondition.value) {
    initializeValue('value', pimCondition.value);
  }

  const isMetric = (value: any): boolean => {
    return (
      typeof value === 'object' &&
      value.hasOwnProperty('amount') &&
      value.hasOwnProperty('unit')
    );
  };

  const isPrice = (value: any): boolean => {
    return (
      typeof value === 'object' &&
      value.hasOwnProperty('amount') &&
      value.hasOwnProperty('currency')
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

  const displayLocale = (locale: string | null): ReactElement | null => {
    if (null === locale) {
      return null;
    }

    return (
      <>
        <Flag locale={locale} flagDescription={locale} /> {locale}
      </>
    );
  };

  return (
    <div className='AknRule'>
      <span className='AknRule-attribute'>{pimCondition.field}</span>
      {` ${translate(
        `pimee_catalog_rule.form.edit.conditions.operators.${pimCondition.operator}`
      )} `}
      <span className='AknRule-attribute'>
        {displayValue(pimCondition.value)}
      </span>
      {pimCondition.scope || pimCondition.locale ? (
        pimCondition.scope && pimCondition.locale ? (
          <span className='AknRule-attribute'>
            {' [ '}
            {displayLocale(pimCondition.locale)}
            {' | '}
            {pimCondition.scope}
            {' ] '}
          </span>
        ) : (
          <span className='AknRule-attribute'>
            {' [ '}
            {displayLocale(pimCondition.locale)}
            {pimCondition.scope}
            {' ] '}
          </span>
        )
      ) : (
        ''
      )}
    </div>
  );
};

export { PimConditionLine };
