import React from 'react';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';
import {SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const BooleanFilterValue: TableFilterValueRenderer = ({value, onChange}) => {
  const translate = useTranslate();
  const stringValue = typeof value === 'undefined' || value === null ? null : value ? 'yes' : 'no';
  const handleChange = (stringValue: string | null) => {
    onChange(stringValue === null ? undefined : stringValue === 'yes');
  };

  return (
    <SelectInput
      value={stringValue}
      openLabel={translate('pim_common.open')}
      emptyResultLabel={translate('pim_common.no_result')}
      onChange={handleChange}
      placeholder={translate('pim_table_attribute.datagrid.select_your_value')}
      clearLabel={translate('pim_common.clear_value')}>
      <SelectInput.Option title={translate('pim_common.yes')} value={'yes'}>
        {translate('pim_common.yes')}
      </SelectInput.Option>
      <SelectInput.Option title={translate('pim_common.no')} value={'no'}>
        {translate('pim_common.no')}
      </SelectInput.Option>
    </SelectInput>
  );
};

const useValueRenderer: FilteredValueRenderer = () => {
  const translate = useTranslate();

  return value => {
    return typeof value === 'undefined' ? '' : translate(`pim_common.${value ? 'yes' : 'no'}`);
  };
};

export {useValueRenderer};
export default BooleanFilterValue;
