import {ColumnDefinitionSelector} from './ColumnDefinitionSelector';
import {RowSelector} from './RowSelector';
import {OperatorSelector} from './OperatorSelector';
import {ValueSelector} from './ValueSelector';
import React, {useState} from 'react';
import styled, {css} from 'styled-components';
import {
  ColumnDefinition,
  FilterOperator,
  FilterValue,
  PendingTableFilterValue,
  SelectOption,
  TableAttribute,
} from '../models';
import {FilterValuesMapping} from './FilterValues';
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
  attribute: TableAttribute;
  filterValuesMapping: FilterValuesMapping;
  onChange: (value: PendingTableFilterValue) => void;
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
    onChange(newFilter);
  };

  const handleColumnChange = (column: ColumnDefinition | undefined) => {
    updateFilter({...filter, column, operator: undefined, value: undefined});
  };

  const handleOperatorChange = (operator: FilterOperator | undefined) => {
    updateFilter({...filter, operator, value: undefined});
  };

  const handleRowChange = (row: SelectOption | undefined | null) => {
    updateFilter({...filter, row});
  };

  const handleValueChange = (value?: FilterValue) => {
    updateFilter({...filter, value});
  };

  return (
    <FilterSelectorListContainer inline={inline}>
      <RowSelector attribute={attribute} value={filter.row} onChange={handleRowChange} />
      <ColumnDefinitionSelector attribute={attribute} onChange={handleColumnChange} value={filter.column} />
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
