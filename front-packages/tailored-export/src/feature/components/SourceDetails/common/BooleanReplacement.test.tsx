import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {BooleanReplacement, isBooleanReplacementOperation} from './BooleanReplacement';

test('it can replace a boolean value', () => {
  const onOperationChange = jest.fn();

  renderWithProviders(
    <BooleanReplacement
      trueLabel="yes"
      falseLabel="no"
      operation={{type: 'replacement', mapping: {true: 'true', false: 'false'}}}
      validationErrors={[]}
      onOperationChange={onOperationChange}
    />
  );

  userEvent.type(screen.getByLabelText('yes'), '{backspace}');

  expect(onOperationChange).toHaveBeenCalledWith({type: 'replacement', mapping: {true: 'tru', false: 'false'}});

  userEvent.type(screen.getByLabelText('no'), 'f');

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
      propertyPath: '[mapping][true]',
    },
    {
      messageTemplate: 'error.key.false',
      invalidValue: '',
      message: 'this is a false error',
      parameters: {},
      propertyPath: '[mapping][false]',
    },
  ];

  renderWithProviders(
    <BooleanReplacement
      trueLabel="yes"
      falseLabel="no"
      validationErrors={validationErrors}
      onOperationChange={onOperationChange}
    />
  );

  expect(screen.getByText('error.key.true')).toBeInTheDocument();
  expect(screen.getByText('error.key.false')).toBeInTheDocument();
});

test('it can tell if something is a valid boolean replacement operation', () => {
  expect(
    isBooleanReplacementOperation({
      type: 'replacement',
      mapping: {
        true: 'true',
        false: 'false',
      },
    })
  ).toBe(true);
  expect(
    isBooleanReplacementOperation({
      type: 'something_else',
      key: {
        aucun: 'rapport',
      },
    })
  ).toBe(false);
});
