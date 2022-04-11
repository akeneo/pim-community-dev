import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DecimalSeparatorField, isDecimalSeparator} from './DecimalSeparatorField';

test('it can tell if something is a decimal separator', () => {
  expect(isDecimalSeparator('٫‎')).toEqual(true);
  expect(isDecimalSeparator('.')).toEqual(true);
  expect(isDecimalSeparator(',')).toEqual(true);
  expect(isDecimalSeparator('#')).toEqual(false);
  expect(isDecimalSeparator('')).toEqual(false);
});

test('it displays all decimal separators when opening the dropdown', () => {
  renderWithProviders(<DecimalSeparatorField value="." onChange={jest.fn()} validationErrors={[]} />);

  userEvent.click(screen.getByTitle('pim_common.open'));

  expect(
    screen.getAllByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.dot')
  ).toHaveLength(2);
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.comma')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.arabic_comma')
  ).toBeInTheDocument();
});

test('it calls the handler when selecting a separator', () => {
  const handleChange = jest.fn();

  renderWithProviders(<DecimalSeparatorField value="." onChange={handleChange} validationErrors={[]} />);

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.comma'));

  expect(handleChange).toHaveBeenCalledWith(',');
});

test('it displays validation errors', () => {
  renderWithProviders(
    <DecimalSeparatorField
      value="."
      onChange={jest.fn()}
      validationErrors={[
        {
          messageTemplate: 'error.key.decimal_separator',
          invalidValue: '#',
          message: 'this is a decimal separator error',
          parameters: {},
          propertyPath: '[target][decimal_separator]',
        },
      ]}
    />
  );

  expect(screen.getByText('error.key.decimal_separator')).toBeInTheDocument();
});
