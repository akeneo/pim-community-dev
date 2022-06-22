import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {
  BooleanReplacementOperation,
  BooleanReplacementOperationBlock,
  getDefaultBooleanReplacementOperation,
} from './BooleanReplacementOperationBlock';
import userEvent from '@testing-library/user-event';

test('it can get the default boolean replacement operation', () => {
  expect(getDefaultBooleanReplacementOperation()).toEqual({
    uuid: expect.any(String),
    type: 'boolean_replacement',
    mapping: {
      false: ['0'],
      true: ['1'],
    },
  });
});

test('it displays a boolean_replacement operation block', () => {
  renderWithProviders(
    <BooleanReplacementOperationBlock
      targetCode="auto_exposure"
      operation={{
        uuid: 'an-uuid',
        type: 'boolean_replacement',
        mapping: {
          false: ['0'],
          true: ['1'],
        },
      }}
      onChange={jest.fn()}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: [],
      }}
      validationErrors={[]}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.boolean_replacement.title')
  ).toBeInTheDocument();
});

test('it updates mapping value', () => {
  const onChange = jest.fn();
  const operation: BooleanReplacementOperation = {
    uuid: 'an-uuid',
    type: 'boolean_replacement',
    mapping: {
      false: ['0'],
      true: ['1'],
    },
  };

  renderWithProviders(
    <BooleanReplacementOperationBlock
      targetCode="auto_exposure"
      operation={operation}
      onChange={onChange}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: [],
      }}
      validationErrors={[]}
    />
  );

  const inputYes = screen.getByLabelText(
    'akeneo.tailored_import.data_mapping.operations.boolean_replacement.field.yes_value'
  );
  userEvent.type(inputYes, 'oui{enter}');

  expect(onChange).toHaveBeenCalledWith({
    ...operation,
    mapping: {
      true: ['1', 'oui'],
      false: ['0'],
    },
  });

  const inputNo = screen.getByLabelText(
    'akeneo.tailored_import.data_mapping.operations.boolean_replacement.field.no_value'
  );
  userEvent.type(inputNo, 'non{enter}');

  expect(onChange).toHaveBeenCalledWith({
    ...operation,
    mapping: {
      true: ['1'],
      false: ['0', 'non'],
    },
  });
});

test('it throws an error if the operation is not a boolean replacement operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <BooleanReplacementOperationBlock
        targetCode="auto_exposure"
        operation={{
          uuid: 'an-uuid',
          type: 'clean_html_tags',
        }}
        onChange={jest.fn()}
        onRemove={jest.fn()}
        isLastOperation={false}
        previewData={{
          isLoading: false,
          hasError: false,
          data: [],
        }}
        validationErrors={[]}
      />
    );
  }).toThrowError('BooleanReplacementOperationBlock can only be used with BooleanReplacementOperation');

  mockedConsole.mockRestore();
});

test('it displays validation errors', () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.true_error',
      invalidValue: '',
      message: 'this is a true error',
      parameters: {},
      propertyPath: '[mapping][true]',
    },
    {
      messageTemplate: 'error.key.false_error',
      invalidValue: '',
      message: 'this is a false error',
      parameters: {},
      propertyPath: '[mapping][false]',
    },
  ];

  renderWithProviders(
    <BooleanReplacementOperationBlock
      targetCode="auto_exposure"
      operation={{
        uuid: 'an-uuid',
        type: 'boolean_replacement',
        mapping: {
          false: ['0'],
          true: ['1'],
        },
      }}
      onChange={jest.fn()}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: [],
      }}
      validationErrors={validationErrors}
    />
  );

  expect(screen.getByText('error.key.true_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.false_error')).toBeInTheDocument();
});
