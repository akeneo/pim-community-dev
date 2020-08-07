import React from 'react';
import { Operator } from '../../../../../models/actions/Calculate/Operation';
import {
  Select2SimpleSyncWrapper,
  Select2Value,
} from '../../../../../components/Select2Wrapper';
import { Translate } from '../../../../../dependenciesTools';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { CurrencyCode } from '../../../../../models/Currency';

const buildData = (translate: Translate) =>
  Object.values(Operator).map((operator: string) => {
    return {
      id: operator,
      text: translate(
        `pimee_catalog_rule.form.edit.actions.calculate.operator.${operator}`
      ).toUpperCase(),
    };
  });

type Props = {
  name: string;
  value: Operator;
  onChange?: (value: CurrencyCode) => void;
  label?: string;
  hiddenLabel?: boolean;
};

const CalculateOperatorSelector: React.FC<Props> = ({
  name,
  value,
  onChange,
  label,
  hiddenLabel = false,
  ...remainingProps
}) => {
  const translate = useTranslate();

  const handleChange = (operator: Select2Value) => {
    if (onChange) {
      onChange(operator as Operator);
    }
  };

  return (
    <Select2SimpleSyncWrapper
      {...remainingProps}
      label={label || translate('pimee_catalog_rule.form.edit.fields.operator')}
      hiddenLabel={hiddenLabel || false}
      hideSearch
      name={name}
      data={buildData(translate)}
      value={value}
      onChange={handleChange}
      dropdownCssClass='calculate-operator-dropdown'
    />
  );
};

export { CalculateOperatorSelector };
