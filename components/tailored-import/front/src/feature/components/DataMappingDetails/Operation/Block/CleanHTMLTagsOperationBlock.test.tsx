import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CleanHTMLTagsOperationBlock, getDefaultCleanHTMLTagsOperation} from './CleanHTMLTagsOperationBlock';
import {OperationPreviewData} from 'feature/models';

const operationPreviewData: OperationPreviewData = {
  [expect.any(String)]: [
    {type: 'string', value: '<p>Hello</p>'},
    {type: 'string', value: '<p>World</p>'},
  ],
};

test('it can get the default clean html tags operation', () => {
  expect(getDefaultCleanHTMLTagsOperation()).toEqual({
    uuid: expect.any(String),
    type: 'clean_html_tags',
  });
});

test('it displays a clean html tags operation block', () => {
  renderWithProviders(
    <CleanHTMLTagsOperationBlock
      targetCode="name"
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

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html_tags.title')).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <CleanHTMLTagsOperationBlock
      targetCode="name"
      operation={{uuid: expect.any(String), type: 'clean_html_tags'}}
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

  expect(handleRemove).toHaveBeenCalledWith('clean_html_tags');
});

test('it displays a preview data section when having preview data', () => {
  renderWithProviders(
    <CleanHTMLTagsOperationBlock
      targetCode="name"
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

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.preview.button'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(screen.getByText('<p>Hello</p>')).toBeInTheDocument();
});
