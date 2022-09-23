import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CleanHTMLTags, isCleanHTMLTagsOperation, isDefaultCleanHTMLTagsOperation} from './CleanHTMLTags';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

test('it can check if something is a valid clean HTML tags operation', () => {
  expect(
    isCleanHTMLTagsOperation({
      type: 'clean_html_tags',
      value: true,
    })
  ).toBe(true);

  expect(
    isCleanHTMLTagsOperation({
      type: 'clean_html_tags',
      value: 'azerty',
    })
  ).toBe(false);

  expect(
    isCleanHTMLTagsOperation({
      type: 'something_else',
      key: {
        aucun: 'rapport',
      },
    })
  ).toBe(false);
});

test('it can check is something is a default clean HTML tags operation', () => {
  expect(
    isDefaultCleanHTMLTagsOperation({
      what: 'ever',
    })
  ).toBe(false);

  expect(
    isDefaultCleanHTMLTagsOperation({
      type: 'clean_html_tags',
      value: true,
    })
  ).toBe(false);

  expect(
    isDefaultCleanHTMLTagsOperation({
      type: 'clean_html_tags',
      value: false,
    })
  ).toBe(true);
});

describe('it can choose to clean html tags', () => {
  const onOperationChange = jest.fn();

  test('it update operation with true when checkbox is check', () => {
    renderWithProviders(
      <CleanHTMLTags operation={{type: 'clean_html_tags', value: false}} onOperationChange={onOperationChange} />
    );

    userEvent.click(
      screen.getByText('akeneo.syndication.data_mapping_details.sources.operation.clean_html_tags.label')
    );

    expect(onOperationChange).toHaveBeenCalledWith({type: 'clean_html_tags', value: true});
  });

  test('it set operation to undefined when checkbox is uncheck', () => {
    renderWithProviders(
      <CleanHTMLTags operation={{type: 'clean_html_tags', value: true}} onOperationChange={onOperationChange} />
    );

    userEvent.click(
      screen.getByText('akeneo.syndication.data_mapping_details.sources.operation.clean_html_tags.label')
    );

    expect(onOperationChange).toHaveBeenCalledWith(undefined);
  });
});
