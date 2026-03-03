import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {Select} from '../../../Resources/public/js/datagrid/quickexport/component/Select';
import {Option} from '../../../Resources/public/js/datagrid/quickexport/component/Option';

test('it displays its children, selecting the correct Option and ignoring others', () => {
  const {getByText} = renderWithProviders(
    <Select name="gender" value="female">
      <Option value="male">Male</Option>
      <Option value="female">Female</Option>
      <Option value="other">Other</Option>
      <div>I should be untouched</div>
    </Select>
  );

  expect(getByText('I should be untouched')).toBeInTheDocument();
  expect(getByText('Male')).toHaveAttribute('data-selected', 'false');
  expect(getByText('Female')).toHaveAttribute('data-selected', 'true');
});
