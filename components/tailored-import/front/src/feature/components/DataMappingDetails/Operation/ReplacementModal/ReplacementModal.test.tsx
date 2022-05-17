import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {ReplacementModal} from './ReplacementModal';

const values = [
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
];

const validResponse = {
  ok: true,
  json: async () => {},
};

test('it can update a replacement mapping', async () => {
  const handleConfirm = jest.fn();
  global.fetch = jest.fn().mockImplementation(async () => validResponse);

  await renderWithProviders(
    <ReplacementModal
      title="Manage values"
      operationType="simple_select_replacement"
      initialMapping={{}}
      totalItems={3}
      itemsPerPage={25}
      values={values}
      onReplacementValueFilterChange={jest.fn()}
      replacementValueFilter={{searchValue: '', page: 1, codesToInclude: null, codesToExclude: null}}
      onConfirm={handleConfirm}
      onCancel={jest.fn()}
    />
  );

  const [blackInput] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.modal.table.field.to_placeholder'
  );

  userEvent.type(blackInput, 'Noir;');
  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).toHaveBeenCalledWith({
    black: ['Noir'],
  });
});

test('it validates replacement mapping before confirming', async () => {
  const handleConfirm = jest.fn();
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
    json: async () => [
      {propertyPath: '[mapping][black]', messageTemplate: 'error.invalid_value.message', parameters: {}},
    ],
  }));

  await renderWithProviders(
    <ReplacementModal
      title="Manage values"
      operationType="simple_select_replacement"
      initialMapping={{}}
      totalItems={3}
      itemsPerPage={25}
      values={values}
      onReplacementValueFilterChange={jest.fn()}
      replacementValueFilter={{searchValue: '', page: 1, codesToInclude: null, codesToExclude: null}}
      onConfirm={handleConfirm}
      onCancel={jest.fn()}
    />
  );

  const [blackInput] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.modal.table.field.to_placeholder'
  );

  userEvent.type(blackInput, 'invalid_mapping');
  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).not.toHaveBeenCalled();
  expect(screen.getByText('error.invalid_value.message')).toBeInTheDocument();
});

test('it can filter search results', async () => {
  jest.useFakeTimers();

  const handleReplacementValueFilterChange = jest.fn();
  const replacementValueFilter = {searchValue: '', page: 2, codesToInclude: null, codesToExclude: null};
  await renderWithProviders(
    <ReplacementModal
      title="Manage values"
      operationType="simple_select_replacement"
      initialMapping={{}}
      totalItems={3}
      itemsPerPage={1}
      values={values}
      onReplacementValueFilterChange={handleReplacementValueFilterChange}
      replacementValueFilter={replacementValueFilter}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  userEvent.type(screen.getByPlaceholderText('pim_common.search'), 'bl');
  act(() => {
    jest.runAllTimers();
  });

  expect(handleReplacementValueFilterChange.mock.calls).toHaveLength(2);
  expect(handleReplacementValueFilterChange.mock.calls[1][0](replacementValueFilter)).toEqual({
    searchValue: 'bl',
    page: 1,
    codesToInclude: null,
    codesToExclude: null,
  });
});

test('it can show all results', async () => {
  const handleReplacementValueFilterChange = jest.fn();
  const replacementValueFilter = {searchValue: '', page: 1, codesToInclude: null, codesToExclude: null};

  await renderWithProviders(
    <ReplacementModal
      title="Manage values"
      operationType="simple_select_replacement"
      initialMapping={{
        black: ['Noir'],
      }}
      totalItems={3}
      itemsPerPage={25}
      values={values}
      onReplacementValueFilterChange={handleReplacementValueFilterChange}
      replacementValueFilter={replacementValueFilter}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.label:')
  );

  userEvent.click(
    screen.getByTitle('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.all')
  );

  expect(handleReplacementValueFilterChange.mock.calls).toHaveLength(2);
  expect(handleReplacementValueFilterChange.mock.calls[1][0](replacementValueFilter)).toEqual({
    searchValue: '',
    page: 1,
    codesToInclude: null,
    codesToExclude: null,
  });
});

test('it can change page', async () => {
  const handleReplacementValueFilterChange = jest.fn();
  const replacementValueFilter = {searchValue: '', page: 1, codesToInclude: null, codesToExclude: null};

  await renderWithProviders(
    <ReplacementModal
      title="Manage values"
      operationType="simple_select_replacement"
      initialMapping={{
        black: ['Noir'],
      }}
      totalItems={3}
      itemsPerPage={1}
      values={values}
      onReplacementValueFilterChange={handleReplacementValueFilterChange}
      replacementValueFilter={replacementValueFilter}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  userEvent.click(screen.getByTitle('No. 2'));

  expect(handleReplacementValueFilterChange.mock.calls).toHaveLength(2);
  expect(handleReplacementValueFilterChange.mock.calls[1][0](replacementValueFilter)).toEqual({
    searchValue: '',
    page: 2,
    codesToInclude: null,
    codesToExclude: null,
  });
});

test('it can show only mapped results', async () => {
  const handleReplacementValueFilterChange = jest.fn();
  const replacementValueFilter = {searchValue: '', page: 1, codesToInclude: null, codesToExclude: null};

  await renderWithProviders(
    <ReplacementModal
      title="Manage values"
      operationType="simple_select_replacement"
      initialMapping={{
        black: ['Noir'],
      }}
      totalItems={3}
      itemsPerPage={25}
      values={values}
      onReplacementValueFilterChange={handleReplacementValueFilterChange}
      replacementValueFilter={replacementValueFilter}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.label:')
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.mapped')
  );

  expect(handleReplacementValueFilterChange.mock.calls).toHaveLength(2);
  expect(handleReplacementValueFilterChange.mock.calls[1][0](replacementValueFilter)).toEqual({
    searchValue: '',
    page: 1,
    codesToInclude: ['black'],
    codesToExclude: null,
  });
});

test('it can show only unmapped results', async () => {
  const handleReplacementValueFilterChange = jest.fn();
  const replacementValueFilter = {searchValue: '', page: 2, codesToInclude: null, codesToExclude: null};

  await renderWithProviders(
    <ReplacementModal
      title="Manage values"
      operationType="simple_select_replacement"
      initialMapping={{
        black: ['Noir'],
      }}
      totalItems={3}
      itemsPerPage={2}
      values={values}
      onReplacementValueFilterChange={handleReplacementValueFilterChange}
      replacementValueFilter={replacementValueFilter}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.label:')
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.unmapped')
  );

  expect(handleReplacementValueFilterChange.mock.calls).toHaveLength(2);
  expect(handleReplacementValueFilterChange.mock.calls[1][0](replacementValueFilter)).toEqual({
    searchValue: '',
    page: 1,
    codesToInclude: null,
    codesToExclude: ['black'],
  });
});
