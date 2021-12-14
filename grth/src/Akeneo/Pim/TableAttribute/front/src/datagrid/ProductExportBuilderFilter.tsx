import React from 'react';
import {
  ColumnCode,
  FilterOperator,
  FilterValue,
  isFilterValid,
  PendingTableFilterValue,
  SelectOptionCode,
  TableAttribute,
} from '../models';
import {FilterSelectorList} from './FilterSelectorList';
import styled from 'styled-components';
import {useFetchOptions} from '../product';
import {AttributeContext} from '../contexts';

export type BackendTableProductExportFilterValue = {
  operator: FilterOperator;
  value: {
    row?: SelectOptionCode;
    column: ColumnCode;
    value: FilterValue;
  };
};

export type PendingTableProductExportFilterValue = {
  operator?: FilterOperator;
  value?: {
    row?: SelectOptionCode;
    column?: ColumnCode;
    value?: FilterValue;
  };
};

type ProductExportBuilderFilterProps = {
  attribute: TableAttribute;
  onChange: (val: BackendTableProductExportFilterValue) => void;
  initialDataFilter: PendingTableProductExportFilterValue;
};

const FieldContainer = styled.div`
  margin-bottom: 0;
`;

const ProductExportBuilderFilter: React.FC<ProductExportBuilderFilterProps> = ({
  attribute,
  onChange,
  initialDataFilter,
}) => {
  const [attributeState, setAttributeState] = React.useState<TableAttribute>(attribute);
  const {getOptionsFromColumnCode} = useFetchOptions(attributeState, setAttributeState);
  const handleChange = (filter: PendingTableFilterValue) => {
    if (isFilterValid(filter)) {
      onChange({
        operator: filter.operator as FilterOperator,
        value: {
          column: filter.column?.code as ColumnCode,
          value: filter.value as FilterValue,
          row: filter.row?.code,
        },
      });
    }
  };

  const [initialFilter, setInitialFilter] = React.useState<PendingTableFilterValue | undefined>();
  const optionsForFirstColumn = attributeState
    ? getOptionsFromColumnCode(attributeState.table_configuration[0].code)
    : [];

  React.useEffect(() => {
    if (attributeState) {
      const column = attributeState.table_configuration.find(column => column.code === initialDataFilter.value?.column);

      if (typeof optionsForFirstColumn === 'undefined') {
        return;
      }

      const row = optionsForFirstColumn.find(option => option.code === initialDataFilter.value?.row);
      setInitialFilter({
        row,
        column,
        value: initialDataFilter.value?.value,
        operator: initialDataFilter.operator,
      });
    }
  }, [attributeState, optionsForFirstColumn]);

  return (
    <AttributeContext.Provider value={{attribute: attributeState, setAttribute: setAttributeState}}>
      <FieldContainer className='AknFieldContainer AknFieldContainer--big'>
        <div className='AknFieldContainer-inputContainer'>
          {initialFilter && (
            <FilterSelectorList
              onChange={handleChange}
              initialFilter={initialFilter}
              inline={true}
            />
          )}
        </div>
      </FieldContainer>
    </AttributeContext.Provider>
  );
};

export {ProductExportBuilderFilter};
