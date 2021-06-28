import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {BooleanReplacement} from './BooleanReplacement';

test('it can replace a boolean value', () => {
  const onOperationChange = jest.fn();

  renderWithProviders(
    <BooleanReplacement
      operation={{type: 'replacement', mapping: {true: 'true', false: 'false'}}}
      validationErrors={[]}
      onOperationChange={onOperationChange}
    />
  );

  userEvent.type(
    screen.getByLabelText('akeneo.tailored_export.column_details.sources.operation.replacement.enabled'),
    '{backspace}'
  );

  expect(onOperationChange).toHaveBeenCalledWith({type: 'replacement', mapping: {true: 'tru', false: 'false'}});

  userEvent.type(
    screen.getByLabelText('akeneo.tailored_export.column_details.sources.operation.replacement.disabled'),
    'f'
  );

  expect(onOperationChange).toHaveBeenCalledWith({type: 'replacement', mapping: {true: 'true', false: 'falsef'}});
});

test('it displays validation errors', () => {
  const onOperationChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.true',
      invalidValue: '',
      message: 'this is a true error',
      parameters: {},
      propertyPath: '[true]',
    },
    {
      messageTemplate: 'error.key.false',
      invalidValue: '',
      message: 'this is a false error',
      parameters: {},
      propertyPath: '[false]',
    },
  ];

  renderWithProviders(
    <BooleanReplacement
      validationErrors={validationErrors}
      operation={{type: 'replacement', mapping: {true: 'true', false: 'false'}}}
      onOperationChange={onOperationChange}
    />
  );

  expect(screen.getByText('error.key.true')).toBeInTheDocument();
  expect(screen.getByText('error.key.false')).toBeInTheDocument();
});
