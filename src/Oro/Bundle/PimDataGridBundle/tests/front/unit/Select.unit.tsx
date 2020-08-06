import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {Select} from '../../../Resources/public/js/datagrid/quickexport/component/Select';
import {Option} from '../../../Resources/public/js/datagrid/quickexport/component/Option';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';

test('it displays its children, selecting the correct Option and ignoring others', () => {
  const {getByText} = render(
    <AkeneoThemeProvider>
      <Select name="gender" value="female">
        <Option value="male">Male</Option>
        <Option value="female">Female</Option>
        <Option value="other">Other</Option>
        <div>I should be untouched</div>
      </Select>
    </AkeneoThemeProvider>
  );

  expect(getByText('I should be untouched')).toBeInTheDocument();
  expect(getByText('Male')).toHaveAttribute('data-selected', 'false');
  expect(getByText('Female')).toHaveAttribute('data-selected', 'true');
});
