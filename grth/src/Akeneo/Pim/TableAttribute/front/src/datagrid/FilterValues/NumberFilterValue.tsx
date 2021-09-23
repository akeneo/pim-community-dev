import React from 'react';
import {NumberInput} from "akeneo-design-system";

const NumberFilterValue: DatagridTableFilterValueRenderer = ({
  value,
  onChange,
}) => {
  return <NumberInput
    value={value}
    onChange={onChange}
  />
};

export default NumberFilterValue;
