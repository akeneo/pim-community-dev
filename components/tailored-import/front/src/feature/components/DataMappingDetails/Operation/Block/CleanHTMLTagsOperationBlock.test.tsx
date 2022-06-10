import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CleanHTMLTagsOperationBlock, getDefaultCleanHTMLTagsOperation} from './CleanHTMLTagsOperationBlock';

test('it can get the default clean html tags operation', () => {
  expect(getDefaultCleanHTMLTagsOperation()).toEqual({
    uuid: expect.any(String),
    type: 'clean_html_tags',
  });
});

test('it displays a clean html tags operation block', () => {
  const previewData = {
    [expect.any(String)]: ['<p>Hello</p>', '<p>World</p>'],
  };

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
        data: previewData,
      }}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html_tags.title')).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();
  const previewData = {
    [expect.any(String)]: ['<p>Hello</p>', '<p>World</p>'],
  };

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
        data: previewData,
      }}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.remove')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.delete'));

  expect(handleRemove).toHaveBeenCalledWith('clean_html_tags');
});

test('it displays a preview data section when having preview data', () => {
  const previewData = {
    [expect.any(String)]: ['<p>Hello</p>', '<p>World</p>'],
  };

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
        data: previewData,
      }}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.preview.button'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(screen.getByText('<p>Hello</p>')).toBeInTheDocument();
});
