import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {RemoveWhitespaceOperationBlock, getDefaultRemoveWhitespaceOperation} from './RemoveWhitespaceOperationBlock';
import {OperationPreviewData} from 'feature/models';

const operationPreviewData: OperationPreviewData = {
  [expect.any(String)]: [
    {type: 'string', value: '<p>Hello</p>'},
    {type: 'string', value: '<p>World</p>'},
  ],
};

test('it can get the default remove whitespace operation', () => {
  expect(getDefaultRemoveWhitespaceOperation()).toEqual({
    uuid: expect.any(String),
    modes: ['trim'],
    type: 'remove_whitespace',
  });
});

test('it displays a remove whitespace operation block', () => {
  renderWithProviders(
    <RemoveWhitespaceOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), modes: ['trim'], type: 'remove_whitespace'}}
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
    screen.getByText('akeneo.tailored_import.data_mapping.operations.remove_whitespace.title')
  ).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <RemoveWhitespaceOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), modes: ['trim'], type: 'remove_whitespace'}}
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

  expect(handleRemove).toHaveBeenCalledWith('remove_whitespace');
});

test('it displays a preview data section when having preview data', () => {
  renderWithProviders(
    <RemoveWhitespaceOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), modes: ['trim'], type: 'remove_whitespace'}}
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
  expect(screen.getByText('<p>Hello</p>')).toBeInTheDocument();
});

test('it adds a remove whitespace mode', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <RemoveWhitespaceOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), modes: ['trim'], type: 'remove_whitespace'}}
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

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.remove_whitespace.consecutive'));
  expect(handleChange).toHaveBeenCalledWith({
    uuid: expect.any(String),
    type: 'remove_whitespace',
    modes: ['trim', 'consecutive'],
  });
});

test('it removes a remove whitespace mode', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <RemoveWhitespaceOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), modes: ['trim', 'consecutive'], type: 'remove_whitespace'}}
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

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.remove_whitespace.trim'));
  expect(handleChange).toHaveBeenCalledWith({
    uuid: expect.any(String),
    type: 'remove_whitespace',
    modes: ['consecutive'],
  });
});

test('it cant remove a mode if it is the only one checked', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <RemoveWhitespaceOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), modes: ['trim'], type: 'remove_whitespace'}}
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

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.remove_whitespace.trim'));
  expect(handleChange).not.toHaveBeenCalled();
});

test('it throws an error if the operation is not a remove whitespace operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <RemoveWhitespaceOperationBlock
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
  }).toThrowError('RemoveWhitespaceOperationBlock can only be used with RemoveWhitespaceOperation');

  mockedConsole.mockRestore();
});
