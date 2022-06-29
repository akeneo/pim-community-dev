import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {getDefaultSplitOperation, SplitOperationBlock} from './SplitOperationBlock';
import {OperationPreviewData} from 'feature/models';

const operationPreviewData: OperationPreviewData = {
  [expect.any(String)]: [
    {type: 'string', value: 'tee shirt'},
    {type: 'string', value: 't-shirt'},
  ],
};

test('it can get the default split operation', () => {
  expect(getDefaultSplitOperation()).toEqual({
    uuid: expect.any(String),
    type: 'split',
    separator: ',',
  });
});

test('it displays a split operation block', () => {
  renderWithProviders(
    <SplitOperationBlock
      targetCode="brand"
      operation={{uuid: expect.any(String), type: 'split', separator: ','}}
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

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.split.title')).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <SplitOperationBlock
      targetCode="brand"
      operation={{uuid: expect.any(String), type: 'split', separator: ','}}
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

  expect(handleRemove).toHaveBeenCalledWith('split');
});

test('it can change the separator', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <SplitOperationBlock
      targetCode="brand"
      operation={{uuid: expect.any(String), type: 'split', separator: ','}}
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

  userEvent.click(screen.getByTitle('akeneo.tailored_import.data_mapping.operations.common.collapse'));
  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByTitle('semicolon'));

  expect(handleChange).toHaveBeenCalledWith({uuid: expect.any(String), type: 'split', separator: ';'});
});

test('it throws an error if the operation is not a split operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <SplitOperationBlock
        targetCode="brand"
        operation={{uuid: expect.any(String), type: 'clean_html_tags'}}
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
  }).toThrowError('SplitOperationBlock can only be used with SplitOperation');

  mockedConsole.mockRestore();
});
