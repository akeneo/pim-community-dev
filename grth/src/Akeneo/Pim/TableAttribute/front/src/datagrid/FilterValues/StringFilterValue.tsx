import React from 'react';
import {TextInput} from 'akeneo-design-system';
import {DatagridTableFilterValueRenderer} from './index';

const StringFilterValue: DatagridTableFilterValueRenderer = ({value, onChange}) => {
  return <TextInput value={value} onChange={onChange} />;
};

export default StringFilterValue;
