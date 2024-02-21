import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {Form} from '../../../Resources/public/js/datagrid/quickexport/component/Form';
import {Select} from '../../../Resources/public/js/datagrid/quickexport/component/Select';
import {Option} from '../../../Resources/public/js/datagrid/quickexport/component/Option';

test('it displays its children, selecting the correct Option among the Selects and ignoring others', () => {
  const onChange = jest.fn();

  const {getByText, getByRole} = renderWithProviders(
    <Form value={{age: 'old', gender: 'female'}} onChange={onChange}>
      <Select name="age">
        <Option value="young">Young</Option>
        <Option value="old">Old</Option>
      </Select>
      <Select name="gender">
        <Option value="male">Male</Option>
        <Option value="female">Female</Option>
      </Select>
      <div>Useless div</div>
      <Select name="location">
        <Option value="paris">Paris</Option>
        <Option value="nantes">Nantes</Option>
      </Select>
    </Form>
  );

  expect(getByText('Young')).toHaveAttribute('data-selected', 'false');
  expect(getByText('Old')).toHaveAttribute('data-selected', 'true');
  expect(getByRole('gender-select')).toHaveAttribute('data-visible', 'true');
  expect(getByRole('location-select')).toHaveAttribute('data-visible', 'false');
});
