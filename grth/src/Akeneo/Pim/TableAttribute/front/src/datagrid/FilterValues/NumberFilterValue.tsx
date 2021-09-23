import React from 'react';
import {NumberInput} from "akeneo-design-system";
import {DatagridTableFilterValueRenderer} from "./index";

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
