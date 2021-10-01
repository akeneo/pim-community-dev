import {ColumnDefinitionSelector} from "./ColumnDefinitionSelector";
import {RowSelector} from "./RowSelector";
import {OperatorSelector} from "./OperatorSelector";
import {ValueSelector} from "./ValueSelector";
import React, {useState} from "react";
import styled, {css} from "styled-components";
import {ColumnDefinition, SelectOption, TableAttribute} from "../models";
import {FilterValuesMapping} from "./FilterValues";
import {DatagridTableFilterValue, TableFilterValue} from "./DatagridTableFilter";
import {AkeneoThemedProps} from 'akeneo-design-system';

const FilterSelectorListContainer = styled.div<{inline: boolean} & AkeneoThemedProps>`
  ${({inline}) => inline ? css`
     display: flex;
     width: 100%;
  ` : css`
    margin-top: 20px;
    & > * {
      margin-bottom: 10px;
    }
  `
  }
`;

type FilterSelectorListProps = {
  attribute: TableAttribute;
  filterValuesMapping: FilterValuesMapping;
  onChange: (value: TableFilterValue) => void;
  inline?: boolean;
  initialFilter: TableFilterValue;
};

const FilterSelectorList: React.FC<FilterSelectorListProps> = ({
  attribute,
  filterValuesMapping,
  onChange,
  inline = false,
  initialFilter,
}) => {
  const [filter, setFilter] = useState<TableFilterValue>(initialFilter);

  const updateFilter = (newFilter: TableFilterValue) => {
    setFilter(newFilter);
    if (isValid(newFilter)) {
      onChange(newFilter);
    }
  }

  const isValid = (_newFilter: TableFilterValue) => {
    // TODO check if value are correclty filled
    return true;
  }

  const handleColumnChange = (column: ColumnDefinition | undefined) => {
    updateFilter({...filter, column, operator: undefined, value: undefined})
  };

  const handleOperatorChange = (operator: string | undefined) => {
    updateFilter({...filter, operator, value: undefined})
  };

  const handleRowChange = (row: SelectOption | undefined) => {
    updateFilter({...filter, row});
  }

  const handleValueChange = (value: any) => {
    updateFilter({...filter, value});
  }

  return <FilterSelectorListContainer inline={inline}>
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
}

export {FilterSelectorList};
