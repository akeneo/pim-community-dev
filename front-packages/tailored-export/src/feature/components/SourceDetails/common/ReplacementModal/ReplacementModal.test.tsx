import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {ReplacementModal} from './ReplacementModal';
import {ValidationError} from '@akeneo-pim-community/shared';

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
      initialMapping={{}}
      totalItems={10}
      values={values}
      onReplaceValueFilterChange={jest.fn()}
      replaceValueFilter={{searchValue: '', page: 1, codesToInclude: [], codesToExclude: []}}
      validationErrors={[]}
      onConfirm={handleConfirm}
      onCancel={jest.fn()}
    />
  );

  const [blackInput] = screen.getAllByPlaceholderText(
    'akeneo.tailored_export.column_details.sources.operation.replacement.modal.table.field.to_placeholder'
  );

  userEvent.type(blackInput, 'Noir');
  await act(async () => {
    await userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).toHaveBeenCalledWith({
    black: 'Noir',
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
      initialMapping={{}}
      totalItems={10}
      values={values}
      onReplaceValueFilterChange={jest.fn()}
      replaceValueFilter={{searchValue: '', page: 1, codesToInclude: [], codesToExclude: []}}
      validationErrors={[]}
      onConfirm={handleConfirm}
      onCancel={jest.fn()}
    />
  );

  const [blackInput] = screen.getAllByPlaceholderText(
    'akeneo.tailored_export.column_details.sources.operation.replacement.modal.table.field.to_placeholder'
  );

  userEvent.type(blackInput, 'invalid_mapping');
  await act(async () => {
    await userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).not.toHaveBeenCalled();
  expect(screen.getByText('error.invalid_value.message')).toBeInTheDocument();
});

test('it can filter search results', async () => {
  jest.useFakeTimers();

  const handleReplaceValueFilterChange = jest.fn();
  const replaceValueFilter = {searchValue: '', page: 1, codesToInclude: [], codesToExclude: []};
  await renderWithProviders(
    <ReplacementModal
      initialMapping={{}}
      totalItems={10}
      values={values}
      onReplaceValueFilterChange={handleReplaceValueFilterChange}
      replaceValueFilter={replaceValueFilter}
      validationErrors={[]}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  userEvent.type(screen.getByPlaceholderText('pim_common.search'), 'bl');
  act(() => {
    jest.runAllTimers();
  })

  expect(handleReplaceValueFilterChange.mock.calls).toHaveLength(2);
  expect(handleReplaceValueFilterChange.mock.calls[1][0](replaceValueFilter)).toEqual({
    searchValue: 'bl',
    page: 1,
    codesToInclude: [],
    codesToExclude: []
  });
});

test('it can show only mapped results', async () => {
  const handleReplaceValueFilterChange = jest.fn();
  const replaceValueFilter = {searchValue: '', page: 1, codesToInclude: [], codesToExclude: []};

  await renderWithProviders(
    <ReplacementModal
      initialMapping={{
        black: 'Noir',
      }}
      totalItems={10}
      values={values}
      onReplaceValueFilterChange={handleReplaceValueFilterChange}
      replaceValueFilter={replaceValueFilter}
      validationErrors={[]}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  userEvent.click(
    screen.getByLabelText(
      'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.mapped.label:'
    )
  );

  userEvent.click(
    screen.getByText(
      'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.mapped.mapped'
    )
  );

  expect(handleReplaceValueFilterChange.mock.calls).toHaveLength(2);
  expect(handleReplaceValueFilterChange.mock.calls[1][0](replaceValueFilter)).toEqual({
    searchValue: '',
    page: 1,
    codesToInclude: ['black'],
    codesToExclude: []
  });
});

test('it can show only unmapped results', async () => {
  const handleReplaceValueFilterChange = jest.fn();
  const replaceValueFilter = {searchValue: '', page: 1, codesToInclude: [], codesToExclude: []};

  await renderWithProviders(
    <ReplacementModal
      initialMapping={{
        black: 'Noir',
      }}
      totalItems={10}
      values={values}
      onReplaceValueFilterChange={handleReplaceValueFilterChange}
      replaceValueFilter={replaceValueFilter}
      validationErrors={[]}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  userEvent.click(
    screen.getByLabelText(
      'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.mapped.label:'
    )
  );

  userEvent.click(
    screen.getByText(
      'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.mapped.unmapped'
    )
  );

  expect(handleReplaceValueFilterChange.mock.calls).toHaveLength(2);
  expect(handleReplaceValueFilterChange.mock.calls[1][0](replaceValueFilter)).toEqual({
    searchValue: '',
    page: 1,
    codesToInclude: [],
    codesToExclude: ['black']
  });
});

test('it displays validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.blue',
      invalidValue: '',
      message: 'this is a blue error',
      parameters: {},
      propertyPath: '[mapping][blue]',
    },
    {
      messageTemplate: 'error.key.black',
      invalidValue: '',
      message: 'this is a black error',
      parameters: {},
      propertyPath: '[mapping][black]',
    },
  ];

  await renderWithProviders(
    <ReplacementModal
      initialMapping={{}}
      totalItems={10}
      values={values}
      onReplaceValueFilterChange={jest.fn()}
      replaceValueFilter={{searchValue: '', page: 1, codesToInclude: [], codesToExclude: []}}
      validationErrors={validationErrors}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.blue')).toBeInTheDocument();
  expect(screen.getByText('error.key.black')).toBeInTheDocument();
});
