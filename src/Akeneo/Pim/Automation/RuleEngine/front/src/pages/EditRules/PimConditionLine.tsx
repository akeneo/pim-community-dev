import React, {ReactElement} from "react";
import {PimCondition} from "../../models/PimCondition";
import {Flag} from "../../components/Flag/Flag";
import {Translate} from "../../dependenciesTools/provider/applicationDependenciesProvider.type";

type Props = {
  register: any;
  condition: PimCondition,
  lineNumber: number,
  translate: Translate,
}

const PimConditionLine: React.FC<Props> = ({ translate, condition }) => {
  const isMetric = (value: any): boolean => {
    return (typeof(value) === 'object' &&
      value.hasOwnProperty('amount') &&
      value.hasOwnProperty('unit'));
  };

  const isPrice = (value: any): boolean => {
    return (typeof(value) === 'object' &&
      value.hasOwnProperty('amount') &&
      value.hasOwnProperty('currency'));
  };

  const displayValue = (value: any): string => {
    if (null === value || 'undefined' === typeof(value)) {
      return '';
    }
    if (Array.isArray(value)) {
      return (value.map((value) => { return displayValue(value) }).join(', '));
    }
    if (typeof(value) === 'boolean') {
      return value ? translate('pim_common.yes') : translate('pim_common.no');
    }
    if (isMetric(value)) {
      return `${value.amount} ${value.unit}`;
    }
    if (isPrice(value)) {
      return `${value.amount} ${value.currency}`;
    }
    if (typeof(value) === 'object') {
      return JSON.stringify(value);
    }

    return value;
  };

  const displayLocale = (locale: string | null) : ReactElement | null => {
    if (null === locale) {
      return null;
    }

    return <>
      <Flag locale={locale} flagDescription={locale}/>{' '}
      {locale}
    </>
  };

  return (
    <div className="AknRule">
      <span className="AknRule-attribute">{condition.field}</span>
      {` ${translate(`pimee_catalog_rule.form.edit.conditions.operators.${condition.operator}`)} `}
      <span className="AknRule-attribute">{displayValue(condition.value)}</span>
      {(condition.scope || condition.locale) ?
        (condition.scope && condition.locale) ?
          <span className="AknRule-attribute">{' [ '}{displayLocale(condition.locale)}{' | '}{condition.scope}{' ] '}</span> :
          <span className="AknRule-attribute">{' [ '}{displayLocale(condition.locale)}{condition.scope}{' ] '}</span>
      : ''
      }
    </div>
  );
};

export { PimConditionLine }
