import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {AssetCollectionMainMediaSelector} from './AssetCollectionMainMediaSelector';
import {renderWithProviders} from 'feature/tests';

test('it can select the original file name of a media file main media ', async () => {
  const onSelectionChange = jest.fn();
  await renderWithProviders(
    <AssetCollectionMainMediaSelector
      validationErrors={[]}
      selection={{
        type: 'media_file',
        locale: null,
        channel: null,
        property: 'file_key',
        separator: ',',
      }}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.asset_collection.property')
  );
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.type.name'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'media_file',
    locale: null,
    channel: null,
    property: 'original_filename',
    separator: ',',
  });
});

test('it can select the path of a media file main media', async () => {
  const onSelectionChange = jest.fn();
  await renderWithProviders(
    <AssetCollectionMainMediaSelector
      validationErrors={[]}
      selection={{
        type: 'media_file',
        locale: null,
        channel: null,
        property: 'file_key',
        separator: ',',
      }}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.asset_collection.property')
  );
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.type.path'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'media_file',
    locale: null,
    channel: null,
    property: 'file_path',
    separator: ',',
  });
});

test('it can select the media link data with prefix and suffix', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AssetCollectionMainMediaSelector
      validationErrors={[]}
      selection={{
        type: 'media_link',
        locale: null,
        channel: null,
        separator: ',',
        with_prefix_and_suffix: false,
      }}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.asset_collection.with_prefix_and_suffix')
  );

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'media_link',
    locale: null,
    channel: null,
    separator: ',',
    with_prefix_and_suffix: true,
  });
});

test('it can select a channel when it have selected scopable main media selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AssetCollectionMainMediaSelector
      validationErrors={[]}
      selection={{
        type: 'media_link',
        locale: 'en_US',
        channel: 'ecommerce',
        with_prefix_and_suffix: false,
        separator: ',',
      }}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.channel'));
  userEvent.click(screen.getByText('[print]'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'media_link',
    locale: 'en_US',
    channel: 'print',
    with_prefix_and_suffix: false,
    separator: ',',
  });
});

test('it can select a locale when main media is localizable', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AssetCollectionMainMediaSelector
      validationErrors={[]}
      selection={{
        type: 'media_link',
        locale: 'en_US',
        channel: 'ecommerce',
        with_prefix_and_suffix: false,
        separator: ',',
      }}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.locale'));
  userEvent.click(screen.getByText('Français'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'media_link',
    locale: 'fr_FR',
    channel: 'ecommerce',
    with_prefix_and_suffix: false,
    separator: ',',
  });
});
