import React from 'react';
import {TableFilterValueRenderer} from './FilterValues';
import {ColumnCode, DataType, FilterOperator, FilterValue} from '../models';
import {ValuesFilterMapping} from "./FilterValues";

type ValueSelectorProps = {
  dataType?: DataType;
  operator: FilterOperator;
  value?: FilterValue;
  onChange: (value?: FilterValue) => void;
  columnCode: ColumnCode;
};

const ValueSelector: React.FC<ValueSelectorProps> = ({
  value,
  onChange,
  operator,
  dataType,
  columnCode,
}) => {
  const Renderer: TableFilterValueRenderer = (ValuesFilterMapping[dataType || ''] || {})[operator || '']?.default;

  return Renderer ? <Renderer value={value} onChange={onChange} columnCode={columnCode} /> : <></>;
};

export {ValueSelector};
