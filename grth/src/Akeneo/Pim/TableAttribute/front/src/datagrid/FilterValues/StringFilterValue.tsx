import React from 'react';
import {TextInput} from 'akeneo-design-system';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';

const StringFilterValue: TableFilterValueRenderer = ({value, onChange}) => {
  return <TextInput value={value} onChange={onChange} />;
};

const useValueRenderer: FilteredValueRenderer = () => {
  return value => {
    return `"${value || ''}"`;
  };
};

export {useValueRenderer};
export default StringFilterValue;
