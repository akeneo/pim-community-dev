import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {ReplacementModal} from './ReplacementModal';
import {ValidationError} from '@akeneo-pim-community/shared';

const attribute = {
  code: 'color',
  type: 'pim_catalog_simpleselect',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

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
        (0 === includeCodes.length || includeCodes.includes(code)) &&
        !excludeCodes.includes(code)
    ),
    3,
  ],
}));

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
      attribute={attribute}
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

test('it validate replacement mapping before confirm mapping', async () => {
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
      attribute={attribute}
      validationErrors={[]}
      onConfirm={jest.fn()}
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
  expect(screen.getByText('error.invalid_value.message'));
});

test('it can filter search results', async () => {
  jest.useFakeTimers();

  await renderWithProviders(
    <ReplacementModal
      initialMapping={{}}
      attribute={attribute}
      validationErrors={[]}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  act(() => {
    userEvent.type(screen.getByPlaceholderText('pim_common.search'), 'bl');
    jest.runAllTimers();
  });

  expect(screen.getByText('Black')).toBeInTheDocument();
  expect(screen.getByText('Blue')).toBeInTheDocument();
  expect(screen.queryByText('Red')).not.toBeInTheDocument();
});

test('it can show only mapped results', async () => {
  jest.useFakeTimers();

  await renderWithProviders(
    <ReplacementModal
      initialMapping={{
        black: 'Noir',
      }}
      attribute={attribute}
      validationErrors={[]}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  act(() => {
    userEvent.click(
      screen.getByLabelText(
        'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.mapped.label:'
      )
    );
    jest.runAllTimers();

    userEvent.click(
      screen.getByText(
        'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.mapped.mapped'
      )
    );
    jest.runAllTimers();
  });

  expect(screen.getByText('Black')).toBeInTheDocument();
  expect(screen.getByDisplayValue('Noir')).toBeInTheDocument();
  expect(screen.queryByText('Blue')).not.toBeInTheDocument();
  expect(screen.queryByText('Red')).not.toBeInTheDocument();
});

test('it can show only unmapped results', async () => {
  jest.useFakeTimers();

  await renderWithProviders(
    <ReplacementModal
      initialMapping={{
        black: 'Noir',
      }}
      attribute={attribute}
      validationErrors={[]}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  act(() => {
    userEvent.click(
      screen.getByLabelText(
        'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.mapped.label:'
      )
    );
    jest.runAllTimers();

    userEvent.click(
      screen.getByText(
        'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.mapped.unmapped'
      )
    );
    jest.runAllTimers();
  });

  expect(screen.queryByText('Black')).not.toBeInTheDocument();
  expect(screen.queryByDisplayValue('Noir')).not.toBeInTheDocument();
  expect(screen.getByText('Blue')).toBeInTheDocument();
  expect(screen.getByText('Red')).toBeInTheDocument();
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
      attribute={attribute}
      validationErrors={validationErrors}
      onConfirm={jest.fn()}
      onCancel={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.blue')).toBeInTheDocument();
  expect(screen.getByText('error.key.black')).toBeInTheDocument();
});
