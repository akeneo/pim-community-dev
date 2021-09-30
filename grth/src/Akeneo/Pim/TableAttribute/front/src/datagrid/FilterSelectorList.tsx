import {ColumnDefinitionSelector} from "./ColumnDefinitionSelector";
import {RowSelector} from "./RowSelector";
import {OperatorSelector} from "./OperatorSelector";
import {ValueSelector} from "./ValueSelector";
import React, {useState} from "react";
import styled, {css} from "styled-components";
import {ColumnDefinition, SelectOption, TableAttribute} from "../models";
import {FilterValuesMapping} from "./FilterValues";
import {TableFilterValue} from "./DatagridTableFilter";
import {AkeneoThemedProps} from 'akeneo-design-system';

const FilterSelectorListContainer = styled.div<{inline: boolean} & AkeneoThemedProps>`
  ${({inline}) => inline ? css`
     display: flex;
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
};

const FilterSelectorList: React.FC<FilterSelectorListProps> = ({
  attribute,
  filterValuesMapping,
  onChange,
  inline = false,
}) => {
  const [selectedColumn, setSelectedColumn] = useState<ColumnDefinition | undefined>();
  const [selectedRow, setSelectedRow] = useState<SelectOption | undefined>();
  const [selectedOperator, setSelectedOperator] = useState<string | undefined>();
  const [value, setValue] = useState<any | undefined>();

  const handleChange = () => {
    onChange({
      row: selectedRow,
      column: selectedColumn,
      operator: selectedOperator as string,
      value: value,
    });
  }

  const isValid = () => {
    // TODO check if value are correclty filled
    return true;
  }

  const handleColumnChange = (column: ColumnDefinition | undefined) => {
    setSelectedColumn(column);
    setSelectedOperator(undefined);
    setValue(undefined);
    if (isValid()) {
      handleChange();
    }
  };

  const handleOperatorChange = (operator: string | undefined) => {
    setSelectedOperator(operator);
    setValue(undefined);
    if (isValid()) {
      handleChange();
    }
  };

  const handleRowChange = (row: SelectOption | undefined) => {
    setSelectedRow(row);
    if (isValid()) {
      handleChange();
    }
  }

  const handleValueChange = (value: any) => {
    setValue(value);
    if (isValid()) {
      handleChange();
    }
  }

  return <FilterSelectorListContainer inline={inline}>
    <ColumnDefinitionSelector attribute={attribute} onChange={handleColumnChange} value={selectedColumn} />
    <RowSelector attribute={attribute} value={selectedRow} onChange={handleRowChange} />
    <OperatorSelector
      dataType={selectedColumn?.data_type}
      value={selectedOperator}
      onChange={handleOperatorChange}
      filterValuesMapping={filterValuesMapping}
    />
    {selectedOperator && selectedColumn && (
      <ValueSelector
        dataType={selectedColumn?.data_type}
        operator={selectedOperator}
        onChange={handleValueChange}
        value={value}
        filterValuesMapping={filterValuesMapping}
        columnCode={selectedColumn.code}
        attribute={attribute}
      />
    )}
  </FilterSelectorListContainer>
}

export {FilterSelectorList};
