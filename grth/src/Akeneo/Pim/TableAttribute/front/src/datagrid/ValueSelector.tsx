import React from 'react';
import {TableFilterValueRenderer, ValuesFilterMapping} from './FilterValues';
import {ColumnCode, FilterOperator, FilterValue} from '../models';
import {useAttributeContext} from '../contexts';

type ValueSelectorProps = {
  operator: FilterOperator;
  value?: FilterValue;
  onChange: (value?: FilterValue) => void;
  columnCode: ColumnCode;
};

const ValueSelector: React.FC<ValueSelectorProps> = ({value, onChange, operator, columnCode}) => {
  const {attribute} = useAttributeContext();
  const dataType = attribute?.table_configuration?.find(({code}) => code === columnCode)?.data_type;
  const Renderer: TableFilterValueRenderer = (ValuesFilterMapping[dataType || ''] || {})[operator || '']?.default;

  return Renderer ? <Renderer value={value} onChange={onChange} columnCode={columnCode} /> : <></>;
};

export {ValueSelector};
