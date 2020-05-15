import React from 'react';
import { Select2SimpleSyncWrapper } from '../Select2Wrapper';
import { Operator } from '../../models/Operator';
import { Translate } from '../../dependenciesTools';

type Props = {
  id: string;
  label: string;
  hiddenLabel?: boolean;
  availableOperators: Operator[];
  translate: Translate;
  name: string;
};

const OperatorSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  availableOperators,
  translate,
  name,
}) => {
  const translateOperator = (operator: string): string => {
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

  return (
    <Select2SimpleSyncWrapper
      id={id}
      label={label}
      hiddenLabel={hiddenLabel}
      data={operatorChoices}
      name={name}
    />
  );
};

export { OperatorSelector };
