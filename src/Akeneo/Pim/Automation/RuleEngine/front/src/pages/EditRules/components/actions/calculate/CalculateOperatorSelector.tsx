import React from 'react';
import { Operator } from '../../../../../models/actions/Calculate/Operation';
import {
  Select2Value, Select2Wrapper,
} from '../../../../../components/Select2Wrapper';
import { Translate } from '../../../../../dependenciesTools';
import { useTranslate } from '../../../../../dependenciesTools/hooks';

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
  value: Operator;
  onChange?: (value: Operator) => void;
  label?: string;
  hiddenLabel?: boolean;
};

const CalculateOperatorSelector: React.FC<Props> = ({
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
    <Select2Wrapper
      {...remainingProps}
      label={label || translate('pimee_catalog_rule.form.edit.fields.operator')}
      hiddenLabel={hiddenLabel || false}
      hideSearch
      data={buildData(translate)}
      value={value}
      onChange={handleChange}
      dropdownCssClass='calculate-operator-dropdown'
      placeholder={translate(
        `pimee_catalog_rule.form.edit.actions.calculate.operator.choose`
      )}
      multiple={false}
    />
  );
};

export { CalculateOperatorSelector };
