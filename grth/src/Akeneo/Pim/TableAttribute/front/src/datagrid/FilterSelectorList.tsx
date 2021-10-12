import {ColumnDefinitionSelector} from './ColumnDefinitionSelector';
import {RowSelector} from './RowSelector';
import {OperatorSelector} from './OperatorSelector';
import {ValueSelector} from './ValueSelector';
import React, {useState} from 'react';
import styled, {css} from 'styled-components';
import {ColumnCode, ColumnDefinition, SelectOption, SelectOptionCode, TableAttribute} from '../models';
import {FilterValuesMapping} from './FilterValues';
import {AkeneoThemedProps} from 'akeneo-design-system';

const FilterSelectorListContainer = styled.div<{inline: boolean} & AkeneoThemedProps>`
  ${({inline}) =>
    inline
      ? css`
          display: flex;
          width: 100%;
          & > div:not(:last-child) {
            input, ul {
              border-right-width: 0;
              border-top-right-radius: 0;
              border-bottom-right-radius: 0;
            }
          }
          & > div:not(:first-child) {
            input, ul {
              border-top-left-radius: 0;
              border-bottom-left-radius: 0;
            }
          }
        `
      : css`
          margin-top: 20px;
          & > * {
            margin-bottom: 10px;
          }
        `}
`;

export type BackendTableFilterValue = {
  row?: SelectOptionCode;
  column: ColumnCode;
  operator: string;
  value: any;
};

export type PendingBackendTableFilterValue = {
  row?: SelectOptionCode;
  column?: ColumnCode;
  operator?: string;
  value?: any;
};

export type PendingTableFilterValue = {
  row?: SelectOption;
  column?: ColumnDefinition;
  operator?: string;
  value?: any;
};

export type ValidTableFilterValue = {
  row?: SelectOption;
  column: ColumnDefinition;
  operator: string;
  value: any;
};

type FilterSelectorListProps = {
  attribute: TableAttribute;
  filterValuesMapping: FilterValuesMapping;
  onChange: (value: ValidTableFilterValue) => void;
  inline?: boolean;
  initialFilter: PendingTableFilterValue;
};

const FilterSelectorList: React.FC<FilterSelectorListProps> = ({
  attribute,
  filterValuesMapping,
  onChange,
  inline = false,
  initialFilter,
}) => {
  const [filter, setFilter] = useState<PendingTableFilterValue>(initialFilter);

  const updateFilter = (newFilter: PendingTableFilterValue) => {
    setFilter(newFilter);
    if (isValid(newFilter)) {
      onChange(newFilter as ValidTableFilterValue);
    }
  };

  const isValid = (newFilter: PendingTableFilterValue) => {
    return (
      typeof newFilter.column !== 'undefined' &&
      typeof newFilter.operator !== 'undefined'
    );
  };

  const handleColumnChange = (column: ColumnDefinition | undefined) => {
    updateFilter({...filter, column, operator: undefined, value: undefined});
  };

  const handleOperatorChange = (operator: string | undefined) => {
    updateFilter({...filter, operator, value: undefined});
  };

  const handleRowChange = (row: SelectOption | undefined) => {
    updateFilter({...filter, row});
  };

  const handleValueChange = (value: any) => {
    updateFilter({...filter, value});
  };

  return (
    <FilterSelectorListContainer inline={inline}>
      <ColumnDefinitionSelector attribute={attribute} onChange={handleColumnChange} value={filter.column} />
      <RowSelector attribute={attribute} value={filter.row} onChange={handleRowChange} />
      <OperatorSelector
        dataType={filter.column?.data_type}
        value={filter.operator}
        onChange={handleOperatorChange}
        filterValuesMapping={filterValuesMapping}
      />
      {filter.operator && filter.column && (
        <ValueSelector
          dataType={filter.column?.data_type}
          operator={filter.operator}
          onChange={handleValueChange}
          value={filter.value}
          filterValuesMapping={filterValuesMapping}
          columnCode={filter.column.code}
          attribute={attribute}
        />
      )}
    </FilterSelectorListContainer>
  );
};

export {FilterSelectorList};
