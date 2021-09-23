import React, {useEffect, useState} from 'react';
import {Button, Dropdown, useBooleanState} from "akeneo-design-system";
import {ColumnCode, ColumnDefinition, SelectOption, SelectOptionCode, TableAttribute} from "../models";
import {AttributeFetcher} from "../fetchers";
import {getLabel, useRouter, useTranslate, useUserContext} from "@akeneo-pim-community/shared";
import {ColumnDefinitionSelector} from "./ColumnDefinitionSelector";
import {ValueSelector} from "./ValueSelector";
import {RowSelector} from "./RowSelector";
import {OperatorSelector} from "./OperatorSelector";
import {FilterValuesMapping} from "./FilterValues";

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
    setSelectedOperator(undefined); // TODO Maybe we can keep the operator if it exists for the new datatype
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

  // TODO Think about wording and translate this
  let criteriaLabel = 'All';
  if (typeof selectedColumn !== 'undefined') {
    criteriaLabel = '';
    criteriaLabel += typeof selectedRow === 'undefined' ? 'Any' : getLabel(selectedRow.labels, catalogLocale, selectedRow.code) + ' ';
    criteriaLabel += getLabel(selectedColumn.labels, catalogLocale, selectedColumn.code) + ' ';
    criteriaLabel += typeof selectedOperator !== 'undefined' ? translate(`pim_common.operators.${selectedOperator}`) + ' ' : '';
    criteriaLabel += typeof value !== 'undefined' ? JSON.stringify(value) : '';
  }

  return <Dropdown>
    {isOpen && attribute && <Dropdown.Overlay verticalPosition="down" onClose={close}>
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
      <Button onClick={handleValidate}>{translate('pim_common.update')}</Button>
    </Dropdown.Overlay>}
    <div className='AknFilterBox-filter filter-select' onClick={open}>
      {showLabel &&
      <span className='AknFilterBox-filterLabel'>{label}</span>
      }
      <span className='AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited' title={criteriaLabel}>
        {criteriaLabel}
      </span>
      <span className='AknFilterBox-filterCaret'/>
    </div>
    <div className='filter-criteria dropdown-menu'/>
    {canDisable &&
    <div className='AknFilterBox-disableFilter AknIconButton AknIconButton--remove' onClick={onDisable}/>
    }
    </Dropdown>
}

export {DatagridTableFilter};
