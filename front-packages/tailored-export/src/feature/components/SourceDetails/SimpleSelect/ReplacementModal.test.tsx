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
  useAttributeOptions: (_attributeCode: string, searchValue: string) => [
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
    ].filter(({code}) => code.includes(searchValue)),
    3,
  ],
}));

test('it can update a replacement mapping', async () => {
  const handleConfirm = jest.fn();

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
  userEvent.click(screen.getByText('pim_common.confirm'));

  expect(handleConfirm).toHaveBeenCalledWith({
    black: 'Noir',
  });
});

test('it can filter search results', async () => {
  jest.useFakeTimers();

  const handleConfirm = jest.fn();

  await renderWithProviders(
    <ReplacementModal
      initialMapping={{}}
      attribute={attribute}
      validationErrors={[]}
      onConfirm={handleConfirm}
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
