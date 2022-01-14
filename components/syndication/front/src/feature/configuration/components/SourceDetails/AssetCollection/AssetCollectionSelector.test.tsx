import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {AssetCollectionSelector} from './AssetCollectionSelector';
import {renderWithProviders} from '../../../tests';

test('it displays a type dropdown and a separator dropdown when the selection type is code', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AssetCollectionSelector
      assetFamilyCode="wallpapers"
      validationErrors={[]}
      selection={{type: 'code', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.syndication.data_mapping_details.sources.selection.collection_separator.title')
  ).toBeInTheDocument();
  expect(screen.getByText('pim_common.code')).toBeInTheDocument();
  expect(screen.queryByText('pim_common.locale')).not.toBeInTheDocument();
});

test('it displays a locale dropdown when the selection type is label', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AssetCollectionSelector
      assetFamilyCode="wallpapers"
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(screen.getByText('pim_common.locale')).toBeInTheDocument();

  userEvent.click(screen.getByLabelText('pim_common.locale'));
  userEvent.click(screen.getByText('Français'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'label', locale: 'fr_FR', separator: ','});
});

test('it can select a label selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AssetCollectionSelector
      assetFamilyCode="wallpapers"
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
    <AssetCollectionSelector
      assetFamilyCode="wallpapers"
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('pim_common.code'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'code', separator: ','});
});

test('it can select a media file selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AssetCollectionSelector
      assetFamilyCode="wallpapers"
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.syndication.data_mapping_details.sources.selection.type.main_media'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'media_file',
    locale: null,
    channel: null,
    property: 'file_key',
    separator: ',',
  });
});

test('it can select a media link selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AssetCollectionSelector
      assetFamilyCode="pokemons"
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.syndication.data_mapping_details.sources.selection.type.main_media'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'media_link',
    locale: null,
    channel: null,
    with_prefix_and_suffix: false,
    separator: ',',
  });
});

test('onSelectionChange callback should not be called if assetFamily is null', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AssetCollectionSelector
      assetFamilyCode="unkown_family"
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.syndication.data_mapping_details.sources.selection.type.main_media'));

  expect(onSelectionChange).not.toBeCalled();
});

test('it can select a collection separator', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AssetCollectionSelector
      assetFamilyCode="wallpapers"
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByText('akeneo.syndication.data_mapping_details.sources.selection.collection_separator.title')
  );
  userEvent.click(
    screen.getByTitle('akeneo.syndication.data_mapping_details.sources.selection.collection_separator.semicolon')
  );

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'label', locale: 'en_US', separator: ';'});
});

test('it displays validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.global',
      invalidValue: '',
      message: 'this is a global error',
      parameters: {},
      propertyPath: '',
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
    {
      messageTemplate: 'error.key.type',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
    {
      messageTemplate: 'error.key.channel',
      invalidValue: '',
      message: 'this is a channel error',
      parameters: {},
      propertyPath: '[channel]',
    },
    {
      messageTemplate: 'error.key.property',
      invalidValue: '',
      message: 'this is a property error',
      parameters: {},
      propertyPath: '[property]',
    },
  ];

  await renderWithProviders(
    <AssetCollectionSelector
      assetFamilyCode="wallpapers"
      validationErrors={validationErrors}
      selection={{type: 'media_file', channel: 'ecommerce', locale: 'en_US', property: 'file_key', separator: ','}}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.global')).toBeInTheDocument();
  expect(screen.getByText('error.key.separator')).toBeInTheDocument();
  expect(screen.getByText('error.key.locale')).toBeInTheDocument();
  expect(screen.getByText('error.key.type')).toBeInTheDocument();
  expect(screen.getByText('error.key.channel')).toBeInTheDocument();
  expect(screen.getByText('error.key.property')).toBeInTheDocument();
  expect(screen.getByRole('alert')).toBeInTheDocument();
});
