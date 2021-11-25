import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {DateSelector} from './DateSelector';

test('it can select a date format', () => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <DateSelector selection={{format: 'yyyy-mm-dd'}} validationErrors={[]} onSelectionChange={onSelectionChange} />
  );

  userEvent.click(screen.getByLabelText('akeneo.tailored_export.column_details.sources.selection.format'));
  userEvent.click(screen.getByTitle('dd.m.yy'));

  expect(onSelectionChange).toHaveBeenCalledWith({format: 'dd.m.yy'});
});

test('it displays validation errors', () => {
  const onSelectionChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.format',
      invalidValue: '',
      message: 'this is a format error',
      parameters: {},
      propertyPath: '[format]',
    },
  ];

  renderWithProviders(
    <DateSelector
      validationErrors={validationErrors}
      selection={{format: 'yyyy-mm-dd'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.format')).toBeInTheDocument();
});
