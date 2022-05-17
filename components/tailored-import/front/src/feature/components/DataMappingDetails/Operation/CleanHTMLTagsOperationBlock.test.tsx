import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CleanHTMLTagsOperationBlock, getDefaultCleanHTMLTagsOperation} from './CleanHTMLTagsOperationBlock';

test('it can get the default clean html tags operation', () => {
  expect(getDefaultCleanHTMLTagsOperation()).toEqual({
    type: 'clean_html_tags',
  });
});

test('it displays a clean html tags operation block', () => {
  renderWithProviders(
    <CleanHTMLTagsOperationBlock
      targetCode="name"
      operation={{type: 'clean_html_tags'}}
      onChange={jest.fn()}
      onRemove={jest.fn()}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html_tags.title')).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <CleanHTMLTagsOperationBlock
      targetCode="name"
      operation={{type: 'clean_html_tags'}}
      onChange={jest.fn()}
      onRemove={handleRemove}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.remove')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.delete'));

  expect(handleRemove).toHaveBeenCalledWith('clean_html_tags');
});
