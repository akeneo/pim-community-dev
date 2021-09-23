import React from 'react';
import {DatagridTableFilterValueRenderer} from "./index";
import {SelectInput} from "akeneo-design-system";
import {useTranslate} from "@akeneo-pim-community/shared";

const BooleanFilterValue: DatagridTableFilterValueRenderer = ({
  value,
  onChange,
}) => {
  const translate = useTranslate();
  const stringValue = typeof value === 'undefined' || value === null ? null : (value ? 'yes' : 'no');
  const handleChange = (stringValue: string | null) => {
    onChange(stringValue === null ? undefined : (stringValue === 'yes'));
  }

  return <SelectInput
    value={stringValue}
    openLabel={translate('pim_common.open')}
    emptyResultLabel={translate('pim_common.no_result')}
    onChange={handleChange}
    clearLabel={translate('pim_common.clear_value')}
  >
    <SelectInput.Option title={translate('pim_common.yes')} value={'yes'}>{translate('pim_common.yes')}</SelectInput.Option>
    <SelectInput.Option title={translate('pim_common.no')} value={'no'}>{translate('pim_common.no')}</SelectInput.Option>
  </SelectInput>
};

export default BooleanFilterValue;
