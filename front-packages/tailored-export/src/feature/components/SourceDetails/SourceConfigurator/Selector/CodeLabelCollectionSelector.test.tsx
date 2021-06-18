import React, {ReactNode} from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders as baseRender, ValidationError} from '@akeneo-pim-community/shared';
import {CodeLabelCollectionSelector} from './CodeLabelCollectionSelector';
import {Attribute} from '../../../../models';
import {FetcherContext} from '../../../../contexts';

const attributes = [{code: 'description', type: 'pim_catalog_text', labels: {}, scopable: false, localizable: false}];
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
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node})</FetcherContext.Provider>));

test('it displays a type dropdown and a separator dropdown when the selection type is code', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={[]}
      selection={{type: 'code', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_export.column_details.sources.selection.separator')).toBeInTheDocument();
  expect(screen.getByText('pim_common.code')).toBeInTheDocument();
  expect(screen.queryByText('pim_common.locale')).not.toBeInTheDocument();
});

test('it displays a locale dropdown when the selection type is label', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(screen.getByText('pim_common.locale')).toBeInTheDocument();

  userEvent.click(screen.getByLabelText('pim_common.locale'));
  userEvent.click(screen.getByText('fr_FR'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'label', locale: 'fr_FR', separator: ','});
});

test('it can select a label selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={[]}
      selection={{type: 'code', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('pim_common.label'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'label', locale: 'en_US', separator: ','});
});

test('it can select a code selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('pim_common.code'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'code', separator: ','});
});

test('it can select a collection separator', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.selection.separator'));
  userEvent.click(screen.getByTitle(';'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'label', locale: 'en_US', separator: ';'});
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
      messageTemplate: 'error.key.type',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
  ];

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={validationErrors}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.separator')).toBeInTheDocument();
  expect(screen.getByText('error.key.locale')).toBeInTheDocument();
  expect(screen.getByText('error.key.type')).toBeInTheDocument();
});
