import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, act} from '@testing-library/react';
import {renderDOMWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {MassUploader} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/mass-uploader';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';

jest.mock('pimui/js/security-context', () => ({
  isGranted: () => {
    return true;
  },
}));

jest.mock('akeneoassetmanager/infrastructure/saver/asset', () => ({
  create: () => {
    return new Promise(resolve => resolve());
  },
}));

jest.mock('akeneoassetmanager/tools/notify', () => ({
  default: () => {},
}));

const imageAttribute = {
  identifier: 'image_attribute_identifier',
  type: MEDIA_FILE_ATTRIBUTE_TYPE,
};
jest.mock('pim/router', () => ({
  redirectToRoute: () => {},
}));

const context = {locale: 'en_US', channel: 'ecommerce'};

const assetFamily = {
  identifier: 'packshot',
  labels: {
    en_US: 'Packshot',
  },
  attributeAsMainMedia: 'image_attribute_identifier',
  attributes: [imageAttribute],
};

const spin = async (container, selector, shouldExist = true) => {
  const maxDate = new Date().getTime() + 5000;
  while (maxDate > new Date().getTime()) {
    await new Promise(resolve => setTimeout(resolve, 50));
    if (shouldExist ? null !== container.querySelector(selector) : null === container.querySelector(selector)) {
      return;
    }
  }

  throw new Error(`Cannot find ${selector}`);
};

const dataProvider = {
  assetFamilyFetcher: {
    fetch: () => {
      return new Promise(resolve => {
        act(() => {
          resolve({
            assetFamily,
            permission: {
              assetFamilyIdentifier: 'packshot',
              edit: true,
            },
          });
        });
      });
    },
  },
  channelFetcher: {
    fetchAll: () => {
      return new Promise(resolve => {
        act(() => {
          resolve([
            {
              code: 'ecommerce',
              locales: [],
            },
          ]);
        });
      });
    },
  },
};

test('It renders a mass uploader button and cancel it', async () => {
  const container = document.createElement('div');
  document.body.appendChild(container);

  await act(async () => {
    renderDOMWithProviders(
      <MassUploader
        assetFamilyIdentifier={'packshot'}
        context={context}
        onAssetCreated={() => {}}
        dataProvider={dataProvider}
      />,
      container
    );
  });

  spin(container, '[title="pim_asset_manager.asset_collection.upload_asset"]');
  fireEvent.click(container.querySelector('[title="pim_asset_manager.asset_collection.upload_asset"]'));

  expect(container.querySelector('[title="pim_asset_manager.asset.upload.confirm"]')).toBeDefined();

  spin(container, '[title="pim_asset_manager.close"]');
  fireEvent.click(container.querySelector('[title="pim_asset_manager.close"]'));

  spin(container, '[title="pim_asset_manager.close"]');

  expect(container.querySelector('[title="pim_asset_manager.close"]')).toBeNull();

  document.body.removeChild(container);
});
