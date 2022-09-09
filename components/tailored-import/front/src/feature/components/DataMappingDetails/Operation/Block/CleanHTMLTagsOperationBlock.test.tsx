import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CleanHTMLOperationBlock, getDefaultCleanHTMLOperation} from './CleanHTMLOperationBlock';
import {OperationPreviewData} from 'feature/models';
import {ChangeCaseOperationBlock} from './ChangeCaseOperationBlock';

const operationPreviewData: OperationPreviewData = {
  [expect.any(String)]: [
    {type: 'string', value: '<p>Hello</p>'},
    {type: 'string', value: '<p>World</p>'},
  ],
};

test('it can get the default clean html tags operation', () => {
  expect(getDefaultCleanHTMLOperation()).toEqual({
    uuid: expect.any(String),
    modes: ['remove', 'decode'],
    type: 'clean_html',
  });
});

test('it displays a clean html tags operation block', () => {
  renderWithProviders(
    <CleanHTMLOperationBlock
      targetCode="name"
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

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html.title')).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <CleanHTMLOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), modes: ['remove'], type: 'clean_html'}}
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

  expect(handleRemove).toHaveBeenCalledWith('clean_html');
});

test('it displays a preview data section when having preview data', () => {
  renderWithProviders(
    <CleanHTMLOperationBlock
      targetCode="name"
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

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.preview.button'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(screen.getByText('<p>Hello</p>')).toBeInTheDocument();
});

test('it adds a clean html mode', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <CleanHTMLOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), modes: ['remove'], type: 'clean_html'}}
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

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html.decode'));
  expect(handleChange).toHaveBeenCalledWith({
    uuid: expect.any(String),
    type: 'clean_html',
    modes: ['remove', 'decode'],
  });
});

test('it removes a clean html mode', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <CleanHTMLOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), modes: ['remove', 'decode'], type: 'clean_html'}}
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

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html.remove'));
  expect(handleChange).toHaveBeenCalledWith({uuid: expect.any(String), type: 'clean_html', modes: ['decode']});
});

test('it cant remove a mode if it is the only one checked', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <CleanHTMLOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), modes: ['remove'], type: 'clean_html'}}
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

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html.remove'));
  expect(handleChange).not.toHaveBeenCalled();
});

test('it throws an error if the operation is not a clean html operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <CleanHTMLOperationBlock
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
  }).toThrowError('CleanHTMLOperationBlock can only be used with CleanHTMLOperation');

  mockedConsole.mockRestore();
});
