import {ColumnDefinitionSelector} from './ColumnDefinitionSelector';
import {RowSelector} from './RowSelector';
import {OperatorSelector} from './OperatorSelector';
import {ValueSelector} from './ValueSelector';
import React, {useState} from 'react';
import styled, {css} from 'styled-components';
import {
  ColumnCode,
  FilterOperator,
  FilterValue,
  PendingBackendTableFilterValue,
  RecordCode,
  SelectOptionCode,
} from '../models';
import {AkeneoThemedProps} from 'akeneo-design-system';

const FilterSelectorListContainer = styled.div<{inline: boolean} & AkeneoThemedProps>`
  ${({inline}) =>
    inline
      ? css`
          display: flex;
          width: 100%;
          & > div:not(:last-child) {
            input,
            ul {
              border-right-width: 0;
              border-top-right-radius: 0;
              border-bottom-right-radius: 0;
            }
          }
          & > div:not(:first-child) {
            input,
            ul {
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

type FilterSelectorListProps = {
  onChange: (value: PendingBackendTableFilterValue) => void;
  inline?: boolean;
  initialFilter: PendingBackendTableFilterValue;
};

const FilterSelectorList: React.FC<FilterSelectorListProps> = ({onChange, inline = false, initialFilter}) => {
  const [filter, setFilter] = useState<PendingBackendTableFilterValue>(initialFilter);

  const updateFilter = (newFilter: PendingBackendTableFilterValue) => {
    setFilter(newFilter);
    onChange(newFilter);
  };

  const handleColumnChange = (column: ColumnCode | undefined) => {
    updateFilter({...filter, column, operator: undefined, value: undefined});
  };

  const handleOperatorChange = (operator: FilterOperator | undefined) => {
    updateFilter({...filter, operator, value: undefined});
  };

  const handleRowChange = (row: SelectOptionCode | RecordCode | undefined | null) => {
    updateFilter({...filter, row});
  };

  const handleValueChange = (value?: FilterValue) => {
    updateFilter({...filter, value});
  };

  return (
    <FilterSelectorListContainer inline={inline}>
      <RowSelector value={filter.row} onChange={handleRowChange} />
      <ColumnDefinitionSelector onChange={handleColumnChange} value={filter.column} />
      <OperatorSelector columnCode={filter.column} value={filter.operator} onChange={handleOperatorChange} />
      {filter.operator && filter.column && (
        <ValueSelector
          operator={filter.operator}
          onChange={handleValueChange}
          value={filter.value}
          columnCode={filter.column}
        />
      )}
    </FilterSelectorListContainer>
  );
};

export {FilterSelectorList};
