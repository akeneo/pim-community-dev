import React from 'react';
import {FilterValuesMapping, TableFilterValueRenderer} from './FilterValues';
import {ColumnCode, DataType, FilterOperator, FilterValue} from '../models';

type ValueSelectorProps = {
  dataType?: DataType;
  operator: FilterOperator;
  value?: FilterValue;
  onChange: (value?: FilterValue) => void;
  filterValuesMapping: FilterValuesMapping;
  columnCode: ColumnCode;
};

const ValueSelector: React.FC<ValueSelectorProps> = ({
  value,
  onChange,
  operator,
  dataType,
  columnCode,
  filterValuesMapping,
}) => {
  const Renderer: TableFilterValueRenderer = (filterValuesMapping[dataType || ''] || {})[operator || '']?.default;

  return Renderer ? <Renderer value={value} onChange={onChange} columnCode={columnCode} /> : <></>;
};

export {ValueSelector};
