import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import Mosaic from 'akeneoassetmanager/application/component/asset/list/mosaic';
import {getAssetEditUrl} from 'akeneoassetmanager/tools/media-url-generator';

const context = {locale: 'en_US', channel: 'ecommerce'};
const assetCollection = [
  {
    asset_family_identifier: 'packshot',
    code: 'Philips22PDL4906H_pack',
    image: [
      {
        attribute: 'image_packshot_99e561de-5ec8-47ba-833c-42e150fe8b7f',
        locale: null,
        channel: null,
        data: {filePath: '', originalFilename: ''},
      },
    ],
    identifier: 'packshot_Philips22PDL4906H_pa_e14f3b03-1929-4109-9b07-68e4f64bba74',
    labels: {en_US: 'YOLO GOAT'},
    completeness: {
      required: 3,
      complete: 2,
    },
  },
  {
    code: 'iphone8_pack',
    image: [
      {
        attribute: 'image_packshot_99e561de-5ec8-47ba-833c-42e150fe8b7f',
        locale: null,
        channel: null,
        data: {filePath: '', originalFilename: ''},
      },
    ],
    asset_family_identifier: 'packshot',
    identifier: 'packshot_iphone8_pack_daadf101-ec94-43a1-8609-2fff24d21c39',
    labels: [],
    completeness: {
      complete: 2,
      required: 3,
    },
  },
  {
    identifier: 'packshot_iphone7_pack_9c35ba44-e4f9-4a48-8250-4c554e6704a4',
    labels: [],
    code: 'iphone7_pack',
    image: [
      {
        attribute: 'image_packshot_99e561de-5ec8-47ba-833c-42e150fe8b7f',
        locale: null,
        channel: null,
        data: {filePath: '', originalFilename: ''},
      },
    ],
    asset_family_identifier: 'packshot',
    completeness: {
      required: 3,
      complete: 2,
    },
  },
];

test('It displays an empty mosaic', () => {
  renderWithProviders(<Mosaic selection={[]} assetCollection={[]} context={context} onSelectionChange={() => {}} />);

  expect(screen.getByText('pim_asset_manager.asset_picker.no_result.title')).toBeInTheDocument();
});

test('It displays an asset collection', () => {
  const {container} = renderWithProviders(
    <Mosaic selection={[]} assetCollection={assetCollection} context={context} onSelectionChange={() => {}} />
  );

  expect(container.querySelectorAll('[data-asset]').length).toEqual(3);
});

test('It displays selected assets', () => {
  const selection = ['iphone7_pack', 'SELECTED_ASSET_NOT_IN_RESULTS'];

  const {container} = renderWithProviders(
    <Mosaic selection={selection} assetCollection={assetCollection} context={context} onSelectionChange={() => {}} />
  );

  expect(container.querySelectorAll('[data-selected="true"]').length).toEqual(1);
});

test('it can add an asset to the selection', () => {
  let newSelection = null;
  const {container} = renderWithProviders(
    <Mosaic
      selection={[]}
      assetCollection={assetCollection}
      context={context}
      onSelectionChange={selectedAssets => {
        newSelection = selectedAssets;
      }}
    />
  );

  const firstCard = container.querySelector('[data-checked]');
  fireEvent.click(firstCard);

  expect(newSelection).toEqual([assetCollection[0].code]);
});

test('it can remove an asset from the selection', () => {
  let newSelection = null;
  const initialSelection = [assetCollection[0].code];
  const {container} = renderWithProviders(
    <Mosaic
      selection={initialSelection}
      assetCollection={assetCollection}
      context={context}
      onSelectionChange={selectedAssets => {
        newSelection = selectedAssets;
      }}
    />
  );

  const firstCard = container.querySelector('[data-checked]');
  fireEvent.click(firstCard);

  expect(newSelection).toEqual([]);
});

test('it can show the assets as links', () => {
  getAssetEditUrl = jest.fn().mockImplementation(asset => '#' + asset.code);

  const {container} = renderWithProviders(
    <Mosaic selection={[]} assetCollection={assetCollection} context={context} assetHasLink={true} />
  );

  const link = container.querySelector('a[href$="#' + assetCollection[0].code + '"]');
  expect(link).not.toBeNull();
  fireEvent.click(link);
});
