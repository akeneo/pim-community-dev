import React from 'react';
import {FilterValuesMapping} from "./FilterValues";
import {ColumnCode, TableAttribute} from "../models";

type ValueSelectorProps = {
  dataType?: string;
  operator: string;
  value?: string;
  onChange: (value: string | null) => void;
  filterValuesMapping: FilterValuesMapping;
  attribute: TableAttribute;
  columnCode: ColumnCode;
}

const ValueSelector: React.FC<ValueSelectorProps> = ({
  value,
  onChange,
  operator,
  dataType,
  attribute,
  columnCode,
  filterValuesMapping,
}) => {
  const Renderer = (filterValuesMapping[dataType || ''] || {})[operator || '']?.default;

  return Renderer ? <Renderer
    value={value}
    onChange={onChange}
    attribute={attribute}
    columnCode={columnCode}
  /> : <></>
}

export {ValueSelector};
