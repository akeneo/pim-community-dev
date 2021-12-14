import React from 'react';
import {FilterSelectorList} from "../datagrid";
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeContext} from "../contexts";
import {PendingBackendTableFilterValue, PendingTableFilterValue, TableAttribute} from "../models";
import {useFetchOptions} from "../product";

type TableAttributeConditionLineProps = {
  attribute?: TableAttribute;
  value: PendingBackendTableFilterValue;
  onChange: (value: PendingBackendTableFilterValue) => void;
}

const TableAttributeConditionLineInput: React.FC<TableAttributeConditionLineProps> = (props) => {
  return <DependenciesProvider>
    <InnerTableAttributeConditionLine {...props}/>
  </DependenciesProvider>
}

const InnerTableAttributeConditionLine: React.FC<TableAttributeConditionLineProps> = ({
  attribute,
  value,
  onChange
}) => {
  const [attributeState, setAttributeState] = React.useState<TableAttribute | undefined>(attribute);
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttributeState);

  if (!attributeState || !getOptionsFromColumnCode(attributeState.table_configuration[0].code)) {
    return <></>;
  }

  const initialFilter = {
    ...value,
    column: attributeState.table_configuration.find(column => column.code === value.column),
    row: getOptionsFromColumnCode(attributeState.table_configuration[0].code)?.find(option => option.code === value.row),
  }

  const handleChange = (value: PendingTableFilterValue) => {
    onChange({
      ...value,
      column: value.column?.code,
      row: value.row?.code,
    });
  }

  return (
    <AttributeContext.Provider value={{attribute: attributeState, setAttribute: setAttributeState}}>
      <FilterSelectorList
        initialFilter={initialFilter}
        inline
        onChange={handleChange}
      />
    </AttributeContext.Provider>
  );
}

export {TableAttributeConditionLineInput};
