import React from 'react';
import {FilterSelectorList} from '../datagrid';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeContext, LocaleCodeContext} from '../contexts';
import {PendingBackendTableFilterValue, PendingTableFilterValue, TableAttribute} from '../models';
import {useFetchOptions} from '../product';
import {useUserContext} from '@akeneo-pim-community/shared';

type TableAttributeConditionLineProps = {
  attribute?: TableAttribute;
  value: PendingBackendTableFilterValue;
  onChange: (value: PendingBackendTableFilterValue) => void;
};

const TableAttributeConditionLineInput: React.FC<TableAttributeConditionLineProps> = props => {
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');

  return (
    <DependenciesProvider>
      <LocaleCodeContext.Provider value={{localeCode: catalogLocale}}>
        <InnerTableAttributeConditionLine {...props} />
      </LocaleCodeContext.Provider>
    </DependenciesProvider>
  );
};

const InnerTableAttributeConditionLine: React.FC<TableAttributeConditionLineProps> = ({attribute, value, onChange}) => {
  const [attributeState, setAttributeState] = React.useState<TableAttribute | undefined>(attribute);
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttributeState);

  if (!attributeState || !getOptionsFromColumnCode(attributeState.table_configuration[0].code)) {
    return <></>;
  }

  const initialFilter = {
    ...value,
    column: attributeState.table_configuration.find(column => column.code === value.column),
    row: getOptionsFromColumnCode(attributeState.table_configuration[0].code)?.find(
      option => option.code === value.row
    ),
  };

  const handleChange = (value: PendingTableFilterValue) => {
    onChange({
      ...value,
      column: value.column?.code,
      row: value.row?.code,
    });
  };

  return (
    <AttributeContext.Provider value={{attribute: attributeState, setAttribute: setAttributeState}}>
      <FilterSelectorList initialFilter={initialFilter} inline onChange={handleChange} />
    </AttributeContext.Provider>
  );
};

export {TableAttributeConditionLineInput};
