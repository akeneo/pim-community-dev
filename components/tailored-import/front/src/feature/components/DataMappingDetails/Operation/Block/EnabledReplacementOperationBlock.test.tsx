import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {
  EnabledReplacementOperation,
  EnabledReplacementOperationBlock,
  getDefaultEnabledReplacementOperation,
} from './EnabledReplacementOperationBlock';
import userEvent from '@testing-library/user-event';

test('it can get the default enabled replacement operation', () => {
  expect(getDefaultEnabledReplacementOperation()).toEqual({
    uuid: expect.any(String),
    type: 'enabled_replacement',
    mapping: {
      false: ['0'],
      true: ['1'],
    },
  });
});

test('it displays a enabled_replacement operation block', () => {
  renderWithProviders(
    <EnabledReplacementOperationBlock
      targetCode="enabled"
      operation={{
        uuid: 'an-uuid',
        type: 'enabled_replacement',
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
    screen.getByText('akeneo.tailored_import.data_mapping.operations.enabled_replacement.title')
  ).toBeInTheDocument();
});

test('it updates mapping value', () => {
  const onChange = jest.fn();
  const operation: EnabledReplacementOperation = {
    uuid: 'an-uuid',
    type: 'enabled_replacement',
    mapping: {
      false: ['0'],
      true: ['1'],
    },
  };

  renderWithProviders(
    <EnabledReplacementOperationBlock
      targetCode="enabled"
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

  const inputEnabled = screen.getByLabelText(
    'akeneo.tailored_import.data_mapping.operations.enabled_replacement.field.enabled_value'
  );
  userEvent.type(inputEnabled, 'oui{enter}');

  expect(onChange).toHaveBeenCalledWith({
    ...operation,
    mapping: {
      true: ['1', 'oui'],
      false: ['0'],
    },
  });

  const inputDisabled = screen.getByLabelText(
    'akeneo.tailored_import.data_mapping.operations.enabled_replacement.field.disabled_value'
  );
  userEvent.type(inputDisabled, 'non{enter}');

  expect(onChange).toHaveBeenCalledWith({
    ...operation,
    mapping: {
      true: ['1'],
      false: ['0', 'non'],
    },
  });
});

test('it throws an error if the operation is not a enabled replacement operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <EnabledReplacementOperationBlock
        targetCode="enabled"
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
  }).toThrowError('EnabledReplacementOperationBlock can only be used with EnabledReplacementOperation');

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
    <EnabledReplacementOperationBlock
      targetCode="enabled"
      operation={{
        uuid: 'an-uuid',
        type: 'enabled_replacement',
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
