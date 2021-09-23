import React from 'react';
import {DatagridTableFilterValueRenderer} from "./index";
import {SelectInput} from "akeneo-design-system";
import {useTranslate} from "@akeneo-pim-community/shared";

const BooleanFilterValue: DatagridTableFilterValueRenderer = ({
  value,
  onChange,
}) => {
  const translate = useTranslate();

  return <SelectInput
    value={value}
    openLabel={translate('pim_common.open')}
    emptyResultLabel={translate('pim_common.no_result')}
    onChange={onChange}
  >
    <SelectInput.Option title={translate('pim_common.yes')} value={true}>{translate('pim_common.yes')}</SelectInput.Option>
    <SelectInput.Option title={translate('pim_common.no')} value={false}>{translate('pim_common.no')}</SelectInput.Option>
  </SelectInput>
};

export default BooleanFilterValue;
