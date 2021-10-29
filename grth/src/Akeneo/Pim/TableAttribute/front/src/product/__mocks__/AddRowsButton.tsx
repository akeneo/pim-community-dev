import React from 'react';
import {Button} from 'akeneo-design-system';
import {AttributeCode, ColumnCode, SelectOptionCode} from '../../models';

type AddRowsButtonProps = {
  attributeCode: AttributeCode;
  columnCode: ColumnCode;
  checkedOptionCodes: SelectOptionCode[];
  toggleChange: (optionCode: SelectOptionCode) => void;
};

const AddRowsButton: React.FC<AddRowsButtonProps> = ({toggleChange}) => {
  const fakeHandleChange = () => {
    toggleChange('pepper');
  };

  return <Button onClick={fakeHandleChange}>pim_table_attribute.product_edit_form.add_rows</Button>;
};

export {AddRowsButton};
