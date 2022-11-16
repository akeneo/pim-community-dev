import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {
  SearchAndReplaceOperationBlock,
  getDefaultSearchAndReplaceOperation,
  SearchAndReplaceOperation,
  SearchAndReplaceValue,
} from './SearchAndReplaceOperationBlock';
import {OperationPreviewData} from 'feature/models';

const operation: SearchAndReplaceOperation = {
  uuid: expect.any(String),
  replacements: [
    {
      uuid: expect.any(String),
      what: 'replace me',
      with: 'with that',
      case_sensitive: false,
    },
  ],
  type: 'search_and_replace',
};

const operationPreviewData: OperationPreviewData = {
  [expect.any(String)]: [
    {type: 'string', value: 'Hello'},
    {type: 'string', value: 'World'},
  ],
};

jest.mock('./SearchAndReplaceModal', () => ({
  SearchAndReplaceModal: ({
    onConfirm,
    onCancel,
  }: {
    onConfirm: (replacements: SearchAndReplaceValue[]) => void;
    onCancel: () => void;
  }) => (
    <>
      <button
        onClick={() =>
          onConfirm([
            {
              uuid: expect.any(String),
              what: 'replace me',
              with: 'with that',
              case_sensitive: true,
            },
          ])
        }
      >
        Confirm
      </button>
      <button onClick={onCancel}>Cancel</button>
    </>
  ),
}));

test('it can get the default search and replace operation', () => {
  expect(getDefaultSearchAndReplaceOperation()).toEqual({
    uuid: expect.any(String),
    type: 'search_and_replace',
    replacements: [],
  });
});

test('it displays a search and replace operation block', () => {
  renderWithProviders(
    <SearchAndReplaceOperationBlock
      targetCode="name"
      operation={operation}
      onChange={jest.fn()}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: operationPreviewData,
      }}
      validationErrors={[]}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.search_and_replace.title')
  ).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <SearchAndReplaceOperationBlock
      targetCode="name"
      operation={operation}
      onChange={jest.fn()}
      onRemove={handleRemove}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: operationPreviewData,
      }}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.remove')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.delete'));

  expect(handleRemove).toHaveBeenCalledWith('search_and_replace');
});

test('it opens a modal and handles change', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <SearchAndReplaceOperationBlock
      targetCode="name"
      operation={operation}
      onChange={handleChange}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: operationPreviewData,
      }}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));
  userEvent.click(screen.getByText('Confirm'));

  expect(handleChange).toHaveBeenCalledWith({
    ...operation,
    replacements: [
      {
        uuid: expect.any(String),
        what: 'replace me',
        with: 'with that',
        case_sensitive: true,
      },
    ],
  });
});

test('it displays a preview data section when having preview data', () => {
  renderWithProviders(
    <SearchAndReplaceOperationBlock
      targetCode="name"
      operation={operation}
      onChange={jest.fn()}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: operationPreviewData,
      }}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.preview.button'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(screen.getByText('Hello')).toBeInTheDocument();
});

test('it throws an error if the operation is not a search and replace operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <SearchAndReplaceOperationBlock
        targetCode="name"
        operation={{uuid: expect.any(String), type: 'split', separator: ';'}}
        onChange={jest.fn()}
        onRemove={jest.fn()}
        isLastOperation={false}
        previewData={{
          isLoading: false,
          hasError: false,
          data: operationPreviewData,
        }}
        validationErrors={[]}
      />
    );
  }).toThrowError('SearchAndReplaceOperationBlock can only be used with SearchAndReplaceOperation');

  mockedConsole.mockRestore();
});
