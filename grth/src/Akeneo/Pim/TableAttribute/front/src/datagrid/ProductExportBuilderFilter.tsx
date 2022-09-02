import React from 'react';
import {
  ColumnCode,
  FilterOperator,
  FilterValue,
  isFilterValid,
  PendingBackendTableFilterValue,
  SelectOptionCode,
  TableAttribute,
} from '../models';
import {FilterSelectorList} from './FilterSelectorList';
import styled from 'styled-components';
import {AttributeContext} from '../contexts';

export type BackendTableProductExportFilterValue = {
  operator: FilterOperator;
  value: {
    row?: SelectOptionCode;
    column: ColumnCode;
    value?: FilterValue;
  };
};

export type PendingTableProductExportFilterValue = {
  operator?: FilterOperator;
  value?: {
    row?: SelectOptionCode | null;
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

  const handleChange = (filter: PendingBackendTableFilterValue) => {
    if (isFilterValid(filter)) {
      onChange({
        operator: filter.operator as FilterOperator,
        value: {
          column: filter.column as ColumnCode,
          value: filter.value as FilterValue,
          row: filter.row || undefined,
        },
      });
    }
  };

  const filter: PendingBackendTableFilterValue = {
    value: initialDataFilter.value?.value,
    column: initialDataFilter.value?.column,
    operator: initialDataFilter.operator,
    row: initialDataFilter.value?.row,
  };

  return (
    <AttributeContext.Provider value={{attribute: attributeState, setAttribute: setAttributeState}}>
      <FieldContainer className='AknFieldContainer AknFieldContainer--big'>
        <div className='AknFieldContainer-inputContainer'>
          <FilterSelectorList onChange={handleChange} initialFilter={filter} inline={true} />
        </div>
      </FieldContainer>
    </AttributeContext.Provider>
  );
};

export {ProductExportBuilderFilter};
