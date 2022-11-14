import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {SearchAndReplaceModal} from './SearchAndReplaceModal';
import {SearchAndReplaceValue} from './SearchAndReplaceOperationBlock';

const replacements: SearchAndReplaceValue[] = [
  {
    uuid: 'fake-uuid-1',
    what: 'replace m',
    with: 'with that',
    case_sensitive: false,
  },
  {
    uuid: 'fake-uuid-2',
    what: 'another one',
    with: 'with that',
    case_sensitive: true,
  },
];

const validResponse = {
  ok: true,
  json: async () => {},
};

test('it can update a replacement', async () => {
  global.fetch = jest.fn().mockImplementation(async () => validResponse);

  const handleConfirm = jest.fn();

  await renderWithProviders(
    <SearchAndReplaceModal
      operationUuid="fake-operation-uuid"
      initialReplacements={replacements}
      onConfirm={handleConfirm}
      onCancel={jest.fn()}
    />
  );

  const [firstWhat] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.search_and_replace.what.placeholder'
  );

  userEvent.type(firstWhat, 'e');
  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).toHaveBeenCalledWith([
    {
      uuid: expect.any(String),
      what: 'replace me',
      with: 'with that',
      case_sensitive: false,
    },
    {
      uuid: expect.any(String),
      what: 'another one',
      with: 'with that',
      case_sensitive: true,
    },
  ]);
});

test('it validates replacements before confirming', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
    json: async () => [
      {
        propertyPath: '[replacements][fake-uuid-1][what]',
        messageTemplate: 'error.invalid_value.message',
        parameters: {},
      },
    ],
  }));

  const handleConfirm = jest.fn();

  await renderWithProviders(
    <SearchAndReplaceModal
      operationUuid="fake-operation-uuid"
      initialReplacements={replacements}
      onConfirm={handleConfirm}
      onCancel={jest.fn()}
    />
  );

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).not.toHaveBeenCalled();
  expect(screen.getByText('error.invalid_value.message')).toBeInTheDocument();
});

test('it can filter search results on "what" field and update state', async () => {
  global.fetch = jest.fn().mockImplementation(async () => validResponse);

  const handleConfirm = jest.fn();

  await renderWithProviders(
    <SearchAndReplaceModal
      operationUuid="fake-operation-uuid"
      initialReplacements={replacements}
      onConfirm={handleConfirm}
      onCancel={jest.fn()}
    />
  );

  expect(screen.getByDisplayValue('replace m')).toBeInTheDocument();
  expect(screen.getByDisplayValue('another one')).toBeInTheDocument();

  userEvent.type(screen.getByPlaceholderText('pim_common.search'), 'Another');

  expect(
    screen.getAllByPlaceholderText('akeneo.tailored_import.data_mapping.operations.search_and_replace.what.placeholder')
  ).toHaveLength(1);
  expect(screen.queryByDisplayValue('replace m')).not.toBeInTheDocument();
  expect(screen.getByDisplayValue('another one')).toBeInTheDocument();

  userEvent.click(screen.getByRole('checkbox'));
  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).toHaveBeenCalledWith([
    {
      uuid: expect.any(String),
      what: 'replace m',
      with: 'with that',
      case_sensitive: false,
    },
    {
      uuid: expect.any(String),
      what: 'another one',
      with: 'with that',
      case_sensitive: false,
    },
  ]);
});

test('it can filter search results on "with" field', async () => {
  await renderWithProviders(
    <SearchAndReplaceModal
      operationUuid="fake-operation-uuid"
      initialReplacements={replacements}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  expect(screen.getByDisplayValue('replace m')).toBeInTheDocument();
  expect(screen.getByDisplayValue('another one')).toBeInTheDocument();

  userEvent.type(screen.getByPlaceholderText('pim_common.search'), 'WITH');

  expect(
    screen.getAllByPlaceholderText('akeneo.tailored_import.data_mapping.operations.search_and_replace.what.placeholder')
  ).toHaveLength(2);
  expect(screen.getByDisplayValue('replace m')).toBeInTheDocument();
  expect(screen.getByDisplayValue('another one')).toBeInTheDocument();
});

test('it displays a placeholder when finding no result', async () => {
  await renderWithProviders(
    <SearchAndReplaceModal
      operationUuid="fake-operation-uuid"
      initialReplacements={replacements}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  userEvent.type(screen.getByPlaceholderText('pim_common.search'), 'unknown');

  expect(
    screen.queryAllByPlaceholderText(
      'akeneo.tailored_import.data_mapping.operations.search_and_replace.what.placeholder'
    )
  ).toHaveLength(0);
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.replacement.modal.empty_result.title')
  ).toBeInTheDocument();
});
