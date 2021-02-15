import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, act, screen} from '@testing-library/react';
import Basket from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/basket';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

const assetCollectionMock = [
  {
    identifier: 'packshot_iphone7_pack_71d34762-61a4-46ea-a9ae-6cc009a6d54d',
    asset_family_identifier: 'packshot',
    code: 'iphone7_pack',
    labels: {
      en_US: 'Iphone 7',
    },
    image: [
      {
        attribute: 'image_packshot_2fc89c75-61a8-415d-9e58-3dc0ba41e3f9',
        locale: null,
        channel: null,
        data: {filePath: '', originalFilename: ''},
      },
    ],
  },
  {
    identifier: 'packshot_iphone8_pack_4dd1b0e7-6977-413a-b436-9946be212c0d',
    asset_family_identifier: 'packshot',
    code: 'iphone8_pack',
    labels: {
      en_US: 'Iphone 8',
    },
    image: [
      {
        attribute: 'image_packshot_2fc89c75-61a8-415d-9e58-3dc0ba41e3f9',
        locale: null,
        channel: null,
        data: {filePath: '', originalFilename: ''},
      },
    ],
  },
];
const dataProvider = {
  assetFetcher: {
    fetchByCode: () => Promise.resolve(assetCollectionMock),
  },
};
const assetFamilyIdentifier = 'packshot';
const context = {
  channel: 'ecommerce',
  locale: 'en_US',
};
const selection = ['iphone7_pack', 'iphone8_pack'];

test('It can display all the items in the basket', async () => {
  await act(async () => {
    renderWithProviders(
      <Basket
        dataProvider={dataProvider}
        selection={selection}
        assetFamilyIdentifier={assetFamilyIdentifier}
        context={context}
        onRemove={jest.fn()}
        onRemoveAll={jest.fn()}
      />
    );
  });

  expect(screen.getByText('iphone7_pack')).toBeInTheDocument();
  expect(screen.getByText('iphone8_pack')).toBeInTheDocument();
});

test('It can display an empty basket', async () => {
  await act(async () => {
    renderWithProviders(
      <Basket
        dataProvider={dataProvider}
        selection={[]}
        assetFamilyIdentifier={assetFamilyIdentifier}
        context={context}
        onRemove={jest.fn()}
        onRemoveAll={jest.fn()}
      />
    );
  });

  expect(screen.getByText('pim_asset_manager.asset_picker.basket.empty_title')).toBeInTheDocument();
});

test('It can remove an item from the basket', async () => {
  const onRemove = jest.fn();

  await act(async () => {
    renderWithProviders(
      <Basket
        dataProvider={dataProvider}
        selection={selection}
        assetFamilyIdentifier={assetFamilyIdentifier}
        context={context}
        onRemove={onRemove}
        onRemoveAll={jest.fn()}
      />
    );
  });

  fireEvent.click(screen.getAllByTitle('pim_asset_manager.asset_picker.basket.remove_one_asset')[0]);

  expect(onRemove).toHaveBeenCalledWith('iphone7_pack');
});

test('It can remove all the items from the basket', async () => {
  const onRemoveAll = jest.fn();

  await act(async () => {
    renderWithProviders(
      <Basket
        dataProvider={dataProvider}
        selection={selection}
        assetFamilyIdentifier={assetFamilyIdentifier}
        context={context}
        onRemove={jest.fn()}
        onRemoveAll={onRemoveAll}
      />
    );
  });

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_picker.basket.remove_all_assets'));

  expect(onRemoveAll).toHaveBeenCalled();
});
