import React, {ReactNode} from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders as baseRender, ValidationError} from '@akeneo-pim-community/shared';
import {QuantifiedAssociationTypeSelector} from './QuantifiedAssociationTypeSelector';
import {AssociationType, Attribute} from '../../../models';
import {FetcherContext} from '../../../contexts';

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
  {
    code: 'print',
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
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>([])},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve(channels)},
  associationType: {fetchByCodes: (): Promise<AssociationType[]> => Promise.resolve([])},
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node}</FetcherContext.Provider>));

test('it displays a type dropdown, entity type dropdown and a separator dropdown when the selection type is code', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <QuantifiedAssociationTypeSelector
      validationErrors={[]}
      selection={{type: 'code', separator: ',', entity_type: 'products'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.association.entity_type')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.collection_separator.title')
  ).toBeInTheDocument();
  expect(screen.getByText('pim_common.code')).toBeInTheDocument();
  expect(screen.queryByText('pim_common.locale')).not.toBeInTheDocument();
  expect(screen.queryByText('pim_common.channel')).not.toBeInTheDocument();
});

test('it displays a type dropdown, entity type dropdown and a separator dropdown when the selection type is quantity', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <QuantifiedAssociationTypeSelector
      validationErrors={[]}
      selection={{type: 'quantity', separator: ',', entity_type: 'products'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.association.entity_type')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.collection_separator.title')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.quantified_association.quantity')
  ).toBeInTheDocument();
  expect(screen.queryByText('pim_common.locale')).not.toBeInTheDocument();
  expect(screen.queryByText('pim_common.channel')).not.toBeInTheDocument();
});

test('it displays a locale and channel dropdown when the selection type is label', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <QuantifiedAssociationTypeSelector
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ',', entity_type: 'products', channel: 'ecommerce'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.association.entity_type')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.collection_separator.title')
  ).toBeInTheDocument();
  expect(screen.getByText('pim_common.locale')).toBeInTheDocument();
  expect(screen.getByText('pim_common.channel')).toBeInTheDocument();

  userEvent.click(screen.getByLabelText('pim_common.locale'));
  userEvent.click(screen.getByText('fr_FR'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'label',
    locale: 'fr_FR',
    separator: ',',
    entity_type: 'products',
    channel: 'ecommerce',
  });
});

test('it can select a label selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <QuantifiedAssociationTypeSelector
      validationErrors={[]}
      selection={{type: 'code', separator: ',', entity_type: 'products'}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('pim_common.label'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'label',
    locale: 'en_US',
    separator: ',',
    entity_type: 'products',
    channel: 'ecommerce',
  });
});

test('it can select a channel when user select the label', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <QuantifiedAssociationTypeSelector
      validationErrors={[]}
      selection={{type: 'label', separator: ',', entity_type: 'products', channel: 'ecommerce', locale: 'en_US'}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.channel'));
  userEvent.click(screen.getByTitle('[print]'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'label',
    locale: 'en_US',
    separator: ',',
    entity_type: 'products',
    channel: 'print',
  });
});

test('it can select a code selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <QuantifiedAssociationTypeSelector
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ',', entity_type: 'products', channel: 'ecommerce'}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('pim_common.code'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'code', separator: ',', entity_type: 'products'});
});

test('it can select a quantity selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <QuantifiedAssociationTypeSelector
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ',', entity_type: 'products', channel: 'ecommerce'}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(
    screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.quantified_association.quantity')
  );

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'quantity', separator: ',', entity_type: 'products'});
});

test('it can select a collection separator', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <QuantifiedAssociationTypeSelector
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ',', entity_type: 'products', channel: 'ecommerce'}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.collection_separator.title')
  );
  userEvent.click(
    screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.collection_separator.semicolon')
  );

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'label',
    locale: 'en_US',
    separator: ';',
    entity_type: 'products',
    channel: 'ecommerce',
  });
});

test('it can select an entity type when type is code', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <QuantifiedAssociationTypeSelector
      validationErrors={[]}
      selection={{type: 'code', separator: ',', entity_type: 'products'}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.selection.association.entity_type'));

  userEvent.click(screen.getByTitle('pim_common.product_models'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'code', separator: ',', entity_type: 'product_models'});
});

test('it displays validation errors', async () => {
  const onSelectionChange = jest.fn();
  const validationErrors: ValidationError[] = [
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
    {
      messageTemplate: 'error.key.channel',
      invalidValue: '',
      message: 'this is a channel error',
      parameters: {},
      propertyPath: '[channel]',
    },
    {
      messageTemplate: 'error.key.type',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
    {
      messageTemplate: 'error.key.entity_type',
      invalidValue: '',
      message: 'this is an entity type error',
      parameters: {},
      propertyPath: '[entity_type]',
    },
  ];

  await renderWithProviders(
    <QuantifiedAssociationTypeSelector
      validationErrors={validationErrors}
      selection={{type: 'label', locale: 'en_US', separator: ',', entity_type: 'products', channel: 'ecommerce'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.separator')).toBeInTheDocument();
  expect(screen.getByText('error.key.locale')).toBeInTheDocument();
  expect(screen.getByText('error.key.type')).toBeInTheDocument();
  expect(screen.getByText('error.key.entity_type')).toBeInTheDocument();
  expect(screen.getByText('error.key.channel')).toBeInTheDocument();
});
