import React, {FC} from 'react';
import {Operator} from '../../models';

type OperatorSelectorProps = {
  operator: Operator | null;
  onChange: (operator: Operator) => void;
  operators: Operator[];
  invalid?: boolean;
  placeholder?: string;
  fullWidth?: boolean;
};

const OperatorSelector: FC<OperatorSelectorProps> = ({operator, onChange, operators}) => {
  return (
    <>
      <span>OperatorSelectorMock</span>
      <span>Operator = {operator}</span>
      {operators.map(operator => (
        <button
          key={operator}
          onClick={() => {
            onChange(operator);
          }}
        >
          Change operator to {operator}
        </button>
      ))}
    </>
  );
};

export {OperatorSelector};
