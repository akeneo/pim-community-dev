import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {
  getDefaultCategoriesReplacementOperation,
  CategoriesReplacementOperationBlock,
} from './CategoriesReplacementOperationBlock';
import {OperationPreviewData, ReplacementValues} from 'feature/models';

const operationPreviewData: OperationPreviewData = {
  [expect.any(String)]: [
    {type: 'string', value: 'tee shirt'},
    {type: 'string', value: 't-shirt'},
  ],
};

jest.mock('../CategoriesReplacementModal/CategoriesReplacementModal', () => ({
  CategoriesReplacementModal: ({
    onConfirm,
    onCancel,
  }: {
    onConfirm: (replacementValues: ReplacementValues) => void;
    onCancel: () => void;
  }) => (
    <>
      <button onClick={() => onConfirm({shoes: ['chaussure', 'chaussures en daim']})}>Confirm</button>
      <button onClick={onCancel}>Cancel</button>
    </>
  ),
}));

test('it can get the default categories replacement operation', () => {
  expect(getDefaultCategoriesReplacementOperation()).toEqual({
    uuid: expect.any(String),
    type: 'categories_replacement',
    mapping: {},
  });
});

test('it displays a categories_replacement operation block', () => {
  renderWithProviders(
    <CategoriesReplacementOperationBlock
      targetCode="categories"
      operation={{uuid: expect.any(String), type: 'categories_replacement', mapping: {}}}
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
    screen.getByText('akeneo.tailored_import.data_mapping.operations.categories_replacement.title')
  ).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <CategoriesReplacementOperationBlock
      targetCode="categories"
      operation={{uuid: expect.any(String), type: 'categories_replacement', mapping: {}}}
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

  expect(handleRemove).toHaveBeenCalledWith('categories_replacement');
});

test('it opens a replacement modal and handles change', async () => {
  const handleChange = jest.fn();

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => {},
  }));

  renderWithProviders(
    <CategoriesReplacementOperationBlock
      targetCode="categories"
      operation={{uuid: expect.any(String), type: 'categories_replacement', mapping: {}}}
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
    uuid: expect.any(String),
    type: 'categories_replacement',
    mapping: {
      shoes: ['chaussure', 'chaussures en daim'],
    },
  });
});

test('it does not call handler when cancelling', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <CategoriesReplacementOperationBlock
      targetCode="categories"
      operation={{uuid: expect.any(String), type: 'categories_replacement', mapping: {}}}
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
  userEvent.click(screen.getByText('Cancel'));

  expect(handleChange).not.toHaveBeenCalled();
});

test('it throws an error if the operation is not a categories replacement operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <CategoriesReplacementOperationBlock
        targetCode="categories"
        operation={{uuid: expect.any(String), modes: ['remove'], type: 'clean_html'}}
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
  }).toThrowError('CategoriesReplacementOperationBlock can only be used with CategoriesReplacementOperation');

  mockedConsole.mockRestore();
});
