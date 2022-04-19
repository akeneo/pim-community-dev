import React from 'react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ErrorActionInput} from './ErrorActionInput';

test('it displays the error action input', () => {
  const handleChange = jest.fn();
  renderWithProviders(<ErrorActionInput value="skip_value" onChange={handleChange} validationErrors={[]} />);

  expect(screen.getByText('akeneo.tailored_import.global_settings.error_action.helper_message')).toBeInTheDocument();

  userEvent.click(screen.getByText('akeneo.tailored_import.global_settings.error_action.label'));
  userEvent.click(screen.getByText('akeneo.tailored_import.global_settings.error_action.skip_product'));

  expect(handleChange).toBeCalledWith('skip_product');
});

test('it can set the error action', () => {
  const handleChange = jest.fn();
  renderWithProviders(<ErrorActionInput value="skip_product" onChange={handleChange} validationErrors={[]} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.global_settings.error_action.label'));
  userEvent.click(screen.getByText('akeneo.tailored_import.global_settings.error_action.skip_value'));

  expect(handleChange).toBeCalledWith('skip_value');
});

test('it displays the validation errors', async () => {
  const handleChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.name',
      invalidValue: '',
      message: 'this is a validation error',
      parameters: {},
      propertyPath: '',
    },
  ];

  renderWithProviders(
    <ErrorActionInput value="skip_value" onChange={handleChange} validationErrors={validationErrors} />
  );

  expect(screen.getByText('error.key.name')).toBeInTheDocument();
});
