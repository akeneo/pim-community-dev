import React from 'react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ErrorActionInput} from './ErrorActionInput';

const mockUuid = 'd1249682-720e-11ec-90d6-0242ac120003';
jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  uuid: () => mockUuid,
}));

test('it displays the error action input', async () => {
  const handleChange = jest.fn();
  renderWithProviders(<ErrorActionInput value="skip_value" onChange={handleChange} validationErrors={[]} />);

  expect(
    screen.getByText('akeneo.tailored_import.global_settings.error_management.helper_message')
  ).toBeInTheDocument();

  userEvent.click(screen.getByText('akeneo.tailored_import.global_settings.error_management.label'));
  userEvent.click(screen.getByText('akeneo.tailored_import.global_settings.error_management.skip_product'));

  expect(handleChange).toBeCalledWith('skip_product');
});

test('it can set the error action', async () => {
  const handleChange = jest.fn();
  renderWithProviders(<ErrorActionInput value="skip_value" onChange={handleChange} validationErrors={[]} />);

  expect(
    screen.getByText('akeneo.tailored_import.global_settings.error_management.helper_message')
  ).toBeInTheDocument();

  userEvent.click(screen.getByText('akeneo.tailored_import.global_settings.error_management.label'));
  userEvent.click(screen.getByText('akeneo.tailored_import.global_settings.error_management.skip_product'));

  expect(handleChange).toBeCalledWith('skip_product');
});

test('it display the validation errors', async () => {
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
