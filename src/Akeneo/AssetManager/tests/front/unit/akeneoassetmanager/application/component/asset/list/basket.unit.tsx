import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, act} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import Basket from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/basket';

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
    fetchByCode: (assetFamilyIdentifier, selection) => {
      return new Promise(resolve => {
        act(() => {
          resolve(assetCollectionMock);
        });
      });
    },
  },
};
const assetFamilyIdentifier = 'packshot';
const context = {
  channel: 'ecommerce',
  locale: 'en_US',
};
const setSelection = jest.fn();

let container;

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('It can display all the items in the basket', async () => {
  const actualSelection = ['iphone7_pack', 'iphone8_pack'];

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <Basket
          onRemove={jest.fn()}
          onRemoveAll={jest.fn()}
          dataProvider={dataProvider}
          selection={actualSelection}
          assetFamilyIdentifier={assetFamilyIdentifier}
          context={context}
        />
      </ThemeProvider>,
      container
    );
  });

  expect(container.querySelectorAll('li').length).toEqual(2);
});

test('It can display an empty basket', async () => {
  const actualSelection = [];

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <Basket
          onRemove={jest.fn()}
          onRemoveAll={jest.fn()}
          dataProvider={dataProvider}
          selection={actualSelection}
          assetFamilyIdentifier={assetFamilyIdentifier}
          context={context}
        />
      </ThemeProvider>,
      container
    );
  });

  expect(container.querySelectorAll('li').length).toEqual(0);
});

test('It can remove an item from the basket', async () => {
  const actualSelection = ['iphone7_pack', 'iphone8_pack'];
  const onRemove = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <Basket
          onRemove={onRemove}
          onRemoveAll={jest.fn()}
          dataProvider={dataProvider}
          selection={actualSelection}
          assetFamilyIdentifier={assetFamilyIdentifier}
          context={context}
        />
      </ThemeProvider>,
      container
    );
  });

  const removeItemButton = container.querySelector('li[data-code="iphone8_pack"] button');
  expect(removeItemButton).toBeInTheDocument();

  await act(async () => {
    fireEvent.click(removeItemButton);
  });

  expect(onRemove).toHaveBeenCalledWith('iphone8_pack');
});

test('It does nothing when we try to remove an undefined asset', async () => {
  let actualSelection = ['honor'];

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <Basket
          onRemove={jest.fn()}
          onRemoveAll={jest.fn()}
          dataProvider={dataProvider}
          selection={actualSelection}
          assetFamilyIdentifier={assetFamilyIdentifier}
          context={context}
        />
      </ThemeProvider>,
      container
    );
  });

  const removeItemButton = container.querySelector('li[data-code="honor"] button');
  expect(removeItemButton).toBeInTheDocument();

  await act(async () => {
    fireEvent.click(removeItemButton);
  });

  expect(actualSelection).toEqual(['honor']);
});

test('It can remove all the items from the basket', async () => {
  const actualSelection = ['iphone7_pack', 'iphone8_pack'];
  const onRemoveAll = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <Basket
          onRemove={jest.fn()}
          onRemoveAll={onRemoveAll}
          dataProvider={dataProvider}
          selection={actualSelection}
          assetFamilyIdentifier={assetFamilyIdentifier}
          context={context}
        />
      </ThemeProvider>,
      container
    );
  });

  const removeAllButton = container.querySelector(
    'div[title="pim_asset_manager.asset_picker.basket.remove_all_assets"]'
  );
  expect(removeAllButton).toBeInTheDocument();

  await act(async () => {
    fireEvent.click(removeAllButton);
  });

  expect(onRemoveAll).toHaveBeenCalled();
});
