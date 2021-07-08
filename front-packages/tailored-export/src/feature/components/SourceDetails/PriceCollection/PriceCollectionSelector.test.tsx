import React, {ReactNode} from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders as baseRender, ValidationError} from '@akeneo-pim-community/shared';
import {PriceCollectionSelector} from './PriceCollectionSelector';
import {Attribute} from '../../../models';
import {FetcherContext} from '../../../contexts';

const attributes: Attribute[] = [
  {
    code: 'price',
    type: 'pim_catalog_price_collection',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  },
];
const channels: Channel[] = [
  {
    code: 'ecommerce',
    labels: {},
    locales: [
      {
        code: 'en_US',
        label: 'en_US',
        region: 'US',
        language: 'en',
      },
      {
        code: 'fr_FR',
        label: 'fr_FR',
        region: 'FR',
        language: 'fr',
      },
    ],
    category_tree: '',
    conversion_units: [],
    currencies: [],
    meta: {
      created: '',
      form: '',
      id: 1,
      updated: '',
    },
  },
];
const fetchers = {
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>(attributes)},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve(channels)},
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node}</FetcherContext.Provider>));

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
  userEvent.click(screen.getByTitle(';'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'amount', separator: ';'});
});

test('it displays validation type errors', async () => {
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
