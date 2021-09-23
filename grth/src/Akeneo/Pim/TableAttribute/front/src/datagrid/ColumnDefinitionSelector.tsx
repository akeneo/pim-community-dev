import React from 'react';
import {getLabel} from "@akeneo-pim-community/shared";
import {SelectInput} from "akeneo-design-system";
import {ColumnCode, ColumnDefinition, TableAttribute} from "../models";

type ColumnDefinitionSelectorProps = {
  attribute: TableAttribute;
  onChange: (columnDefinition: ColumnDefinition | undefined) => void;
  value?: ColumnDefinition;
}

const ColumnDefinitionSelector: React.FC<ColumnDefinitionSelectorProps> = ({
  attribute,
  onChange,
  value
}) => {
  const handleChange = (columnDefinitionCode: ColumnCode | null) => {
    if (null === columnDefinitionCode) {
      onChange(undefined);
      return;
    }
    const columnDefinition = attribute.table_configuration.find(columnDefinition => columnDefinition.code === columnDefinitionCode);
    columnDefinition && onChange(columnDefinition);
  }

  return <SelectInput
    clearLabel=""
    clearable
    emptyResultLabel="No result found"
    onChange={handleChange}
    placeholder="Please enter a value in the Select input"
    value={value?.code || null}
    openLabel={'Open'}
  >
    {attribute.table_configuration.map(columnDefinition => {
        const label = getLabel(columnDefinition.labels, 'en_US', columnDefinition.code);
        return <SelectInput.Option title="label" value={columnDefinition.code} key={columnDefinition.code}>
          {label}
        </SelectInput.Option>
      }
    )}
  </SelectInput>
}

export {ColumnDefinitionSelector};
