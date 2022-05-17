import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {
  getDefaultSimpleSelectReplacementOperation,
  SimpleSelectReplacementOperationBlock,
} from './SimpleSelectReplacementOperationBlock';

jest.mock('../../../hooks/useAttributeOptions', () => ({
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

test('it can get the default simple select replacement operation', () => {
  expect(getDefaultSimpleSelectReplacementOperation()).toEqual({
    type: 'simple_select_replacement',
    mapping: {},
  });
});

test('it displays a simple_select_replacement operation block', () => {
  renderWithProviders(
    <SimpleSelectReplacementOperationBlock
      targetCode="brand"
      operation={{type: 'simple_select_replacement', mapping: {}}}
      onChange={jest.fn()}
      onRemove={jest.fn()}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.simple_select_replacement.title')
  ).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <SimpleSelectReplacementOperationBlock
      targetCode="brand"
      operation={{type: 'simple_select_replacement', mapping: {}}}
      onChange={jest.fn()}
      onRemove={handleRemove}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.remove')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.delete'));

  expect(handleRemove).toHaveBeenCalledWith('simple_select_replacement');
});

test('it opens a replacement modal and handles change', async () => {
  const handleChange = jest.fn();

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => {},
  }));

  renderWithProviders(
    <SimpleSelectReplacementOperationBlock
      targetCode="brand"
      operation={{type: 'simple_select_replacement', mapping: {}}}
      onChange={handleChange}
      onRemove={jest.fn()}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.simple_select_replacement.edit'));

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.simple_select_replacement.title')
  ).toBeInTheDocument();

  const [blackMapping] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.modal.table.field.to_placeholder'
  );

  userEvent.type(blackMapping, 'noir;noir foncé;');

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleChange).toHaveBeenCalledWith({
    type: 'simple_select_replacement',
    mapping: {
      black: ['noir', 'noir foncé'],
    },
  });
});

test('it does not call handler when cancelling', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <SimpleSelectReplacementOperationBlock
      targetCode="brand"
      operation={{type: 'simple_select_replacement', mapping: {}}}
      onChange={handleChange}
      onRemove={jest.fn()}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.simple_select_replacement.edit'));
  userEvent.click(screen.getByTitle('pim_common.close'));

  expect(handleChange).not.toHaveBeenCalled();
});

test('it throws an error if the operation is not a simple select replacement operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <SimpleSelectReplacementOperationBlock
        targetCode="brand"
        operation={{type: 'clean_html_tags'}}
        onChange={jest.fn()}
        onRemove={jest.fn()}
      />
    );
  }).toThrowError('SimpleSelectReplacementOperationBlock can only be used with SimpleSelectReplacementOperation');

  mockedConsole.mockRestore();
});
