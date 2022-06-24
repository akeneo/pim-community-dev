import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {
  getDefaultMultiSelectReplacementOperation,
  MultiSelectReplacementOperationBlock,
} from './MultiSelectReplacementOperationBlock';

jest.mock('../../../../hooks/useAttributeOptions', () => ({
  useAttributeOptions: (
    _attributeCode: string,
    searchValue: string,
    _page: number,
    includeCodes: string[],
    excludeCodes: string[]
  ) => [
    [
      {
        code: 'black',
        labels: {
          en_US: 'Black',
        },
      },
      {
        code: 'red',
        labels: {
          en_US: 'Red',
        },
      },
      {
        code: 'blue',
        labels: {
          en_US: 'Blue',
        },
      },
    ].filter(
      ({code}) =>
        code.includes(searchValue) &&
        (null === includeCodes || includeCodes.includes(code)) &&
        (null === excludeCodes || !excludeCodes.includes(code))
    ),
    3,
  ],
}));

test('it can get the default multi select replacement operation', () => {
  expect(getDefaultMultiSelectReplacementOperation()).toEqual({
    uuid: expect.any(String),
    type: 'multi_select_replacement',
    mapping: {},
  });
});

test('it displays a multi_select_replacement operation block', () => {
  const previewData = {
    [expect.any(String)]: ['<p>Hello</p>', '<p>World</p>'],
  };

  renderWithProviders(
    <MultiSelectReplacementOperationBlock
      targetCode="brand"
      operation={{uuid: expect.any(String), type: 'multi_select_replacement', mapping: {}}}
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

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.multi_select_replacement.title')
  ).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();
  const previewData = {
    [expect.any(String)]: ['<p>Hello</p>', '<p>World</p>'],
  };

  renderWithProviders(
    <MultiSelectReplacementOperationBlock
      targetCode="brand"
      operation={{uuid: expect.any(String), type: 'multi_select_replacement', mapping: {}}}
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

  expect(handleRemove).toHaveBeenCalledWith('multi_select_replacement');
});

test('it opens a replacement modal and handles change', async () => {
  const handleChange = jest.fn();
  const previewData = {
    [expect.any(String)]: ['<p>Hello</p>', '<p>World</p>'],
  };

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => {},
  }));

  renderWithProviders(
    <MultiSelectReplacementOperationBlock
      targetCode="brand"
      operation={{uuid: expect.any(String), type: 'multi_select_replacement', mapping: {}}}
      onChange={handleChange}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: previewData,
      }}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.multi_select_replacement.title')
  ).toBeInTheDocument();

  const [blackMapping] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder'
  );

  userEvent.type(blackMapping, 'noir;noir foncé;');

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleChange).toHaveBeenCalledWith({
    uuid: expect.any(String),
    type: 'multi_select_replacement',
    mapping: {
      black: ['noir', 'noir foncé'],
    },
  });
});

test('it does not call handler when cancelling', () => {
  const handleChange = jest.fn();
  const previewData = {
    [expect.any(String)]: ['<p>Hello</p>', '<p>World</p>'],
  };

  renderWithProviders(
    <MultiSelectReplacementOperationBlock
      targetCode="brand"
      operation={{uuid: expect.any(String), type: 'multi_select_replacement', mapping: {}}}
      onChange={handleChange}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: previewData,
      }}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));
  userEvent.click(screen.getByTitle('pim_common.close'));

  expect(handleChange).not.toHaveBeenCalled();
});

test('it throws an error if the operation is not a multi select replacement operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const previewData = {
    [expect.any(String)]: ['<p>Hello</p>', '<p>World</p>'],
  };

  expect(() => {
    renderWithProviders(
      <MultiSelectReplacementOperationBlock
        targetCode="brand"
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
  }).toThrowError('MultiSelectReplacementOperationBlock can only be used with MultiSelectReplacementOperation');

  mockedConsole.mockRestore();
});
