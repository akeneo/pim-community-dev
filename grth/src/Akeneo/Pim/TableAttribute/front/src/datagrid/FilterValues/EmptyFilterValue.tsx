import React from 'react';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';

const EmptyFilterValue: TableFilterValueRenderer = () => {
  return <></>;
};

const useValueRenderer: FilteredValueRenderer = () => {
  return '';
};

export {useValueRenderer};
export default EmptyFilterValue;
