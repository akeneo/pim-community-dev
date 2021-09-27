import React from 'react';
import {DatagridTableFilterValueRenderer} from "./index";
import {MultiSelectInput} from "akeneo-design-system";
import {useFetchOptions} from "../../product";
import {getLabel, useUserContext, useTranslate} from "@akeneo-pim-community/shared";

const StringFilterValue: DatagridTableFilterValueRenderer = ({
  value,
  onChange,
  attribute,
  columnCode
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const {getOptionsFromColumnCode} = useFetchOptions(attribute.table_configuration, attribute.code, []);
  const options = getOptionsFromColumnCode(columnCode);

  // TODO Search & pagination CPM-379

  return <MultiSelectInput
    value={value || []}
    openLabel={translate('pim_common.open')}
    emptyResultLabel={translate('pim_common.no_result')}
    removeLabel={translate('pim_common.remove')}
    onChange={onChange}
  >
    {(options || []).map(option => <MultiSelectInput.Option value={option.code} key={option.code}>
      {getLabel(option.labels, catalogLocale, option.code)}
    </MultiSelectInput.Option>)}
  </MultiSelectInput>
};

export default StringFilterValue;
