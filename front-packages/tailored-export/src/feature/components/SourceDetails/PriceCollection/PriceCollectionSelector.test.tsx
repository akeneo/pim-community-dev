import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {PriceCollectionSelector} from './PriceCollectionSelector';
import {renderWithProviders} from 'feature/tests';

test('it displays a type dropdown when the selection type is amount', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <PriceCollectionSelector
      selection={{type: 'amount', separator: ','}}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_export.column_details.sources.selection.type.amount')).toBeInTheDocument();
});

test('it can select a currency selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <PriceCollectionSelector
      selection={{type: 'amount', separator: ','}}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.price.currency_code'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'currency_code', separator: ','});
});

test('it can select a currency label along with a default selected locale', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <PriceCollectionSelector
      selection={{type: 'amount', separator: ','}}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.price.currency_label'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'currency_label', locale: 'en_US', separator: ','});
});

test('it can select a currency label locale', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <PriceCollectionSelector
      selection={{type: 'currency_label', locale: 'en_US', separator: ','}}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.selection.price.currency_locale'));
  userEvent.click(screen.getByText('Français'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'currency_label', locale: 'fr_FR', separator: ','});
});

test('it can select a price collection separator', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <PriceCollectionSelector
      validationErrors={[]}
      selection={{type: 'amount', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.collection_separator.title')
  );
  userEvent.click(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.collection_separator.semicolon')
  );

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'amount', separator: ';'});
});

test('it displays validation errors', async () => {
  const onSelectionChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.type',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
    {
      messageTemplate: 'error.key.separator',
      invalidValue: '',
      message: 'this is a separator error',
      parameters: {},
      propertyPath: '[separator]',
    },
    {
      messageTemplate: 'error.key.locale',
      invalidValue: '',
      message: 'this is a locale error',
      parameters: {},
      propertyPath: '[locale]',
    },
  ];

  await renderWithProviders(
    <PriceCollectionSelector
      selection={{type: 'currency_label', locale: 'en_US', separator: ','}}
      validationErrors={validationErrors}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.type')).toBeInTheDocument();
  expect(screen.getByText('error.key.separator')).toBeInTheDocument();
  expect(screen.getByText('error.key.locale')).toBeInTheDocument();
});
