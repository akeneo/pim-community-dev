import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
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
      null: ['N/A'],
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
          null: ['N/A'],
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
      null: ['N/A'],
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
    />
  );

  const inputYes = screen.getByLabelText(
    'akeneo.tailored_import.data_mapping.operations.boolean_replacement.field.yes_value'
  );
  userEvent.type(inputYes, 'oui{enter}');

  expect(onChange).toHaveBeenCalledWith({
    ...operation,
    mapping: {
      false: ['0'],
      true: ['1', 'oui'],
      null: ['N/A'],
    },
  });

  const inputNo = screen.getByLabelText(
    'akeneo.tailored_import.data_mapping.operations.boolean_replacement.field.no_value'
  );
  userEvent.type(inputNo, 'non{enter}');

  expect(onChange).toHaveBeenCalledWith({
    ...operation,
    mapping: {
      false: ['0'],
      true: ['1', 'oui'],
      null: ['N/A'],
    },
  });

  const inputNull = screen.getByLabelText(
    'akeneo.tailored_import.data_mapping.operations.boolean_replacement.field.null_value'
  );
  userEvent.type(inputNull, 'neant{enter}');

  expect(onChange).toHaveBeenCalledWith({
    ...operation,
    mapping: {
      false: ['0'],
      true: ['1'],
      null: ['N/A', 'neant'],
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
      />
    );
  }).toThrowError('BooleanReplacementOperationBlock can only be used with BooleanReplacementOperation');

  mockedConsole.mockRestore();
});
