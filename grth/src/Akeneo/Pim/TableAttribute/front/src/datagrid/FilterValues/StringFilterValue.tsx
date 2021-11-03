import React from 'react';
import {TextInput} from 'akeneo-design-system';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';
import {useTranslate} from '@akeneo-pim-community/shared';

const StringFilterValue: TableFilterValueRenderer = ({value, onChange}) => {
  const translate = useTranslate();

  return (
    <TextInput
      value={(value as string | undefined) || ''}
      onChange={onChange}
      placeholder={translate('pim_table_attribute.datagrid.select_your_value')}
    />
  );
};

const useValueRenderer: FilteredValueRenderer = () => {
  return value => {
    return `"${value || ''}"`;
  };
};

export {useValueRenderer};
export default StringFilterValue;
