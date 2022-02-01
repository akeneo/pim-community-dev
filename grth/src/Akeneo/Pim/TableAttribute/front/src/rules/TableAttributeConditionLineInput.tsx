import React from 'react';
import {FilterSelectorList} from '../datagrid';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeContext, LocaleCodeContext} from '../contexts';
import {PendingBackendTableFilterValue, TableAttribute} from '../models';
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

  const handleChange = (value: PendingBackendTableFilterValue) => {
    onChange({
      ...value,
      column: value.column,
      row: value.row,
    });
  };

  return (
    <AttributeContext.Provider value={{attribute: attributeState, setAttribute: setAttributeState}}>
      <FilterSelectorList initialFilter={value} inline onChange={handleChange} />
    </AttributeContext.Provider>
  );
};

export {TableAttributeConditionLineInput};
