import React from 'react';
import {SelectInput} from "akeneo-design-system";
import {FilterValuesMapping} from "./FilterValues";

type OperatorSelectorProps = {
  dataType?: string;
  value?: string;
  onChange: (operator: string | undefined) => void;
  filterValuesMapping: FilterValuesMapping;
}

const OperatorSelector: React.FC<OperatorSelectorProps> = ({
  dataType,
  value,
  onChange,
  filterValuesMapping,
}) => {
  const operators = Object.keys(filterValuesMapping[dataType || ''] || {});

  const handleChange = (value: string | null) => {
    onChange(null === value ? undefined : value);
  }

  return <SelectInput
    clearLabel=""
    clearable
    emptyResultLabel="No result found"
    onChange={handleChange}
    placeholder="Please enter a value in the Select input"
    value={value || null}
    openLabel={'Open'}
    readOnly={typeof dataType === 'undefined'}
  >
    {(operators || []).map(operator => {
        return <SelectInput.Option title="label" value={operator} key={operator}>
          {operator}
        </SelectInput.Option>
      }
    )}
  </SelectInput>
}

export {OperatorSelector};
