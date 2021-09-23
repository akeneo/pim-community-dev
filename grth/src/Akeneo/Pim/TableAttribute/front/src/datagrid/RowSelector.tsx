import React from 'react';
import {getLabel} from "@akeneo-pim-community/shared";
import {SelectInput} from "akeneo-design-system";
import {SelectOption, SelectOptionCode, TableAttribute} from "../models";
import {useFetchOptions} from "../product";

type RowSelectorProps = {
  attribute: TableAttribute;
  onChange: (option: SelectOption | undefined) => void;
  value?: SelectOption;
}

const RowSelector: React.FC<RowSelectorProps> = ({
  attribute,
  onChange,
  value,
}) => {
  const {getOptionsFromColumnCode} = useFetchOptions(attribute.table_configuration, attribute.code, []);
  const options = getOptionsFromColumnCode(attribute.table_configuration[0].code);

  const handleChange = (selectOptionCode: SelectOptionCode | null) => {
    if (null === selectOptionCode) {
      onChange(undefined);
      return;
    }
    const option = (options || []).find(option => option.code === selectOptionCode);
    option && onChange(option);
  }

  // TODO Search & pagination

  return <SelectInput
    clearLabel=""
    clearable
    emptyResultLabel="No result found"
    onChange={handleChange}
    placeholder="Please enter a value in the Select input"
    value={value?.code || null}
    openLabel={'Open'}
  >
    {(options || []).map(option => {
        const label = getLabel(option.labels, 'en_US', option.code);
        return <SelectInput.Option title="label" value={option.code} key={option.code}>
          {label}
        </SelectInput.Option>
      }
    )}
  </SelectInput>
}

export {RowSelector};
