import React from 'react';
import {FilterValuesMapping, TableFilterValueRenderer} from './FilterValues';
import {ColumnCode, DataType, FilterOperator, FilterValue, TableAttribute} from '../models';

type ValueSelectorProps = {
  dataType?: DataType;
  operator: FilterOperator;
  value?: FilterValue;
  onChange: (value?: FilterValue) => void;
  filterValuesMapping: FilterValuesMapping;
  attribute: TableAttribute;
  columnCode: ColumnCode;
};

const ValueSelector: React.FC<ValueSelectorProps> = ({
  value,
  onChange,
  operator,
  dataType,
  attribute,
  columnCode,
  filterValuesMapping,
}) => {
  const Renderer: TableFilterValueRenderer = (filterValuesMapping[dataType || ''] || {})[operator || '']?.default;

  return Renderer ? (
    <Renderer value={value} onChange={onChange} attribute={attribute} columnCode={columnCode} />
  ) : (
    <></>
  );
};

export {ValueSelector};
