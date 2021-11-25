import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {NumberSelector} from './NumberSelector';

test('it displays a decimal separator dropdown', () => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <NumberSelector validationErrors={[]} selection={{decimal_separator: ','}} onSelectionChange={onSelectionChange} />
  );

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title')
  ).toBeInTheDocument();
});

test('it can change the decimal separator type', () => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <NumberSelector validationErrors={[]} selection={{decimal_separator: ','}} onSelectionChange={onSelectionChange} />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.decimal_separator.dot'));

  expect(onSelectionChange).toHaveBeenCalledWith({decimal_separator: '.'});
});

test('it displays validation errors', () => {
  const onSelectionChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.decimal_separator',
      invalidValue: '',
      message: 'this is a decimal separator error',
      parameters: {},
      propertyPath: '[decimal_separator]',
    },
  ];

  renderWithProviders(
    <NumberSelector
      validationErrors={validationErrors}
      selection={{decimal_separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.decimal_separator')).toBeInTheDocument();
});
