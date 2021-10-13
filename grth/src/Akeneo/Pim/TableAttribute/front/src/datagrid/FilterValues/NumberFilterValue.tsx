import React from 'react';
import {NumberInput} from 'akeneo-design-system';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';

const NumberFilterValue: TableFilterValueRenderer = ({value, onChange}) => {
  return <NumberInput value={value || ''} onChange={onChange} />;
};

const useValueRenderer: FilteredValueRenderer = () => {
  return value => {
    return `${value}`;
  };
};

export {useValueRenderer};
export default NumberFilterValue;
