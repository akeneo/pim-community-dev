import React from 'react';
import { Select2SimpleSyncWrapper, Select2Value } from '../Select2Wrapper';
import { Operator } from '../../models/Operator';
import { Translate } from '../../dependenciesTools';

type Props = {
  label?: string;
  availableOperators: Operator[];
  translate: Translate;
  value: Operator;
  name: string;
  onChange?: (value: Operator) => void;
  hiddenLabel?: boolean;
};

const OperatorSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  availableOperators,
  translate,
  value,
  onChange,
  name,
}) => {
  const translateOperator = (operator: Operator): string => {
    const label = translate(
      `pimee_catalog_rule.form.edit.conditions.operators.${operator}`
    );

    return label.charAt(0).toUpperCase() + label.slice(1);
  };

  const operatorChoices = availableOperators.map(operator => {
    return {
      id: operator,
      text: translateOperator(operator),
    };
  });

  const handleChange = (value: Select2Value) => {
    if (onChange) {
      onChange(value as Operator);
    }
  }

  return (
    <Select2SimpleSyncWrapper
      label={label || translate('pim_common.operator')}
      hiddenLabel={hiddenLabel}
      data={operatorChoices}
      value={value}
      onChange={handleChange}
      dropdownCssClass={'operator-dropdown'}
      hideSearch={true}
      name={name}
    />
  );
};

export { OperatorSelector };
