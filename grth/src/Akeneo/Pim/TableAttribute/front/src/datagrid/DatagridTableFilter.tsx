import React, {useEffect, useState} from 'react';
import {Button, Dropdown, getColor, SectionTitle, useBooleanState} from "akeneo-design-system";
import {ColumnCode, ColumnDefinition, SelectOption, SelectOptionCode, TableAttribute} from "../models";
import {AttributeFetcher} from "../fetchers";
import {getLabel, useRouter, useTranslate, useUserContext} from "@akeneo-pim-community/shared";
import {ColumnDefinitionSelector} from "./ColumnDefinitionSelector";
import {ValueSelector} from "./ValueSelector";
import {RowSelector} from "./RowSelector";
import {OperatorSelector} from "./OperatorSelector";
import {FilterValuesMapping} from "./FilterValues";
import styled from "styled-components";

const FilterSectionTitleTitle = styled(SectionTitle.Title)`
  color: ${getColor('brand', 100)};
`
const FilterSectionTitle = styled(SectionTitle)`
  border-bottom-color: ${getColor('brand', 100)};
`

const FilterSelectorList = styled.div`
  margin-top: 20px;
  & > * {
    margin-bottom: 10px;
  }
`

const FilterContainer = styled.div`
  width: 280px;
  padding: 0 20px 10px;
`

const FilterButtonContainer = styled.div`
  text-align: center;
`

export type DatagridTableFilterValue = {
  row?: SelectOptionCode;
  column: ColumnCode;
  operator: string;
  value?: any;
}

type DatagridTableFilterProps = {
  showLabel: boolean;
  label: string;
  canDisable: boolean;
  onDisable: () => void;
  attributeCode: string;
  onChange: (value: DatagridTableFilterValue) => void;
  filterValuesMapping: FilterValuesMapping;
}

const DatagridTableFilter: React.FC<DatagridTableFilterProps> = ({
  showLabel,
  label,
  canDisable,
  onDisable,
  attributeCode,
  onChange,
  filterValuesMapping,
}) => {
  const router = useRouter();
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const [isOpen, open, close] = useBooleanState();
  const [attribute, setAttribute] = useState<TableAttribute | undefined>();
  const [selectedColumn, setSelectedColumn] = useState<ColumnDefinition | undefined>();
  const [selectedRow, setSelectedRow] = useState<SelectOption | undefined>();
  const [selectedOperator, setSelectedOperator] = useState<string | undefined>();
  const [value, setValue] = useState<any | undefined>();

  useEffect(() => {
    AttributeFetcher.fetch(router, attributeCode).then(attribute => {
      setAttribute(attribute as TableAttribute);
    });
  }, []);

  const handleColumnChange = (column: ColumnDefinition | undefined) => {
    setSelectedColumn(column);
    setSelectedOperator(undefined);
    setValue(undefined);
  }

  const handleOperatorChange = (operator: string | undefined) => {
    setSelectedOperator(operator);
    setValue(undefined);
  }

  const handleValidate = () => {
    close();
    onChange({
      row: selectedRow?.code,
      column: selectedColumn?.code as ColumnCode,
      operator: selectedOperator as string,
      value: value
    });
  }

  // TODO Think about wording and translate this CPM-378
  let criteriaLabel = 'All';
  if (typeof selectedColumn !== 'undefined') {
    criteriaLabel = '';
    criteriaLabel += typeof selectedRow === 'undefined' ? 'Any' : getLabel(selectedRow.labels, catalogLocale, selectedRow.code) + ' ';
    criteriaLabel += getLabel(selectedColumn.labels, catalogLocale, selectedColumn.code) + ' ';
    criteriaLabel += typeof selectedOperator !== 'undefined' ? translate(`pim_common.operators.${selectedOperator}`) + ' ' : '';
    criteriaLabel += typeof value !== 'undefined' ? JSON.stringify(value) : '';
  }

  return <Dropdown>
    {isOpen && attribute && <Dropdown.Overlay onClose={close}>
      <FilterContainer>
        <FilterSectionTitle title={label}>
          <FilterSectionTitleTitle>{label}</FilterSectionTitleTitle>
        </FilterSectionTitle>
        <FilterSelectorList>
        <ColumnDefinitionSelector attribute={attribute} onChange={handleColumnChange} value={selectedColumn}/>
        <RowSelector attribute={attribute} value={selectedRow} onChange={setSelectedRow}/>
        <OperatorSelector
          dataType={selectedColumn?.data_type}
          value={selectedOperator}
          onChange={handleOperatorChange}
          filterValuesMapping={filterValuesMapping}
        />
        {selectedOperator && selectedColumn &&
        <ValueSelector
          dataType={selectedColumn?.data_type}
          operator={selectedOperator}
          onChange={setValue}
          value={value}
          filterValuesMapping={filterValuesMapping}
          columnCode={selectedColumn.code}
          attribute={attribute}
        />
        }
        </FilterSelectorList>
        <FilterButtonContainer>
          <Button onClick={handleValidate}>{translate('pim_common.update')}</Button>
        </FilterButtonContainer>
      </FilterContainer>
    </Dropdown.Overlay>}
    <div className='AknFilterBox-filter' onClick={open}>
      {showLabel &&
      <span className='AknFilterBox-filterLabel'>{label}</span>
      }
      <span className='AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited' title={criteriaLabel}>
        {criteriaLabel}
      </span>
      <span className='AknFilterBox-filterCaret'/>
    </div>
    {canDisable &&
    <div className='AknFilterBox-disableFilter AknIconButton AknIconButton--remove' onClick={onDisable}/>
    }
    </Dropdown>
}

export {DatagridTableFilter};
