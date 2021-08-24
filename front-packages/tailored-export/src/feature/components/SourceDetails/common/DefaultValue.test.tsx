import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {DefaultValue, isDefaultValueOperation} from './DefaultValue';

test('it can set a default value', () => {
  const onOperationChange = jest.fn();

  renderWithProviders(
    <DefaultValue
      operation={{type: 'default_value', value: ''}}
      validationErrors={[]}
      onOperationChange={onOperationChange}
    />
  );

  userEvent.type(
    screen.getByLabelText('akeneo.tailored_export.column_details.sources.operation.default_value.label'),
    'f'
  );

  expect(onOperationChange).toHaveBeenCalledWith({type: 'default_value', value: 'f'});
});

test('it displays validation errors', () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.value',
      invalidValue: '',
      message: 'this is a default value error',
      parameters: {},
      propertyPath: '[value]',
    },
  ];

  renderWithProviders(<DefaultValue validationErrors={validationErrors} onOperationChange={jest.fn()} />);

  expect(screen.getByText('error.key.value')).toBeInTheDocument();
  expect(screen.getByRole('alert')).toBeInTheDocument();
});

test('it can tell if something is a valid default value operation', () => {
  expect(
    isDefaultValueOperation({
      type: 'default_value',
      value: 'foo',
    })
  ).toBe(true);
  expect(
    isDefaultValueOperation({
      type: 'something_else',
      key: {
        aucun: 'rapport',
      },
    })
  ).toBe(false);
});
