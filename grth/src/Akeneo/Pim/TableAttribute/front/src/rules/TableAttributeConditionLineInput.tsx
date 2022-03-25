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

const TableAttributeConditionLineInput: React.FC<TableAttributeConditionLineProps> = props => (
  <DependenciesProvider>
    <InnerTableAttributeConditionLine {...props} />
  </DependenciesProvider>
);

const InnerTableAttributeConditionLine: React.FC<TableAttributeConditionLineProps> = ({attribute, value, onChange}) => {
  const [attributeState, setAttributeState] = React.useState<TableAttribute | undefined>(attribute);
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');

  const handleChange = (value: PendingBackendTableFilterValue) => {
    /**
     * For backend:
     * - if the value is null or undefined, it means the condition is applied on any row
     * For FilterSelectorList:
     * - if the value is undefined, we see the placeholder and the user have to select one
     * - if the value is null, it means the user have selected "any row"
     * For ReactHookForm:
     * - null values are prohibited.
     * So, we switch the empty value from FilterSelectorList;
     * - if user selects "any row", we send "undefined" to RHF instead of null (which is not allowed by ReactHookForm).
     * - if user removes the condition on column, we send "null" to RHF to have error message to force user to fill it.
     */
    const row = value.row === null ? undefined : value.row || null;
    onChange({
      ...value,
      column: value.column,
      row,
    });
  };

  return (
    <LocaleCodeContext.Provider value={{localeCode: catalogLocale}}>
      <AttributeContext.Provider value={{attribute: attributeState, setAttribute: setAttributeState}}>
        <FilterSelectorList initialFilter={{...value, row: value.row || null}} inline onChange={handleChange} />
      </AttributeContext.Provider>
    </LocaleCodeContext.Provider>
  );
};

export {TableAttributeConditionLineInput};
