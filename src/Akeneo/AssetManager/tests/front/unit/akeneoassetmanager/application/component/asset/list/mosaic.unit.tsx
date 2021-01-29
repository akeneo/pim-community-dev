import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import Mosaic from 'akeneoassetmanager/application/component/asset/list/mosaic';

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
  renderWithProviders(
    <Mosaic
      isItemSelected={jest.fn()}
      selection={[]}
      assetCollection={[]}
      context={context}
      onSelectionChange={() => {}}
    />
  );

  expect(screen.getByText('pim_asset_manager.asset_picker.no_result.title')).toBeInTheDocument();
});

test('It displays an asset collection', () => {
  renderWithProviders(
    <Mosaic
      isItemSelected={jest.fn()}
      selection={[]}
      assetCollection={assetCollection}
      context={context}
      onSelectionChange={() => {}}
    />
  );

  expect(screen.getByText('YOLO GOAT')).toBeInTheDocument();
  expect(screen.getByText('[iphone8_pack]')).toBeInTheDocument();
  expect(screen.getByText('[iphone7_pack]')).toBeInTheDocument();
});

test('It displays selected assets', () => {
  const isItemSelected = jest.fn((assetCode: string) => assetCode === 'iphone7_pack');

  renderWithProviders(
    <Mosaic
      isItemSelected={isItemSelected}
      assetCollection={assetCollection}
      context={context}
      onSelectionChange={() => {}}
    />
  );

  expect(isItemSelected).toHaveBeenCalledTimes(3);
  expect(isItemSelected).toHaveBeenCalledWith('iphone7_pack');
  expect(isItemSelected).toHaveBeenCalledWith('iphone8_pack');
  expect(isItemSelected).toHaveBeenCalledWith('Philips22PDL4906H_pack');
  expect(screen.getByLabelText('[iphone7_pack]')).toHaveAttribute('aria-checked', 'true');
});

test('it can add an asset to the selection', () => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <Mosaic
      isItemSelected={jest.fn()}
      assetCollection={assetCollection}
      context={context}
      onSelectionChange={onSelectionChange}
    />
  );

  fireEvent.click(screen.getByLabelText('[iphone7_pack]'));

  expect(onSelectionChange).toHaveBeenCalledWith('iphone7_pack', true);
});

test('it can remove an asset from the selection', () => {
  const isItemSelected = jest.fn((assetCode: string) => assetCode === 'iphone7_pack');
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <Mosaic
      isItemSelected={isItemSelected}
      assetCollection={assetCollection}
      context={context}
      onSelectionChange={onSelectionChange}
    />
  );

  fireEvent.click(screen.getByLabelText('[iphone7_pack]'));

  expect(onSelectionChange).toHaveBeenCalledWith('iphone7_pack', false);
});
