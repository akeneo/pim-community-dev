import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {MassUploader} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/mass-uploader';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';

jest.mock('pimui/js/security-context', () => ({
  isGranted: () => true,
}));

jest.mock('akeneoassetmanager/tools/notify', jest.fn());

const imageAttribute = {
  identifier: 'image_attribute_identifier',
  type: MEDIA_FILE_ATTRIBUTE_TYPE,
};

const context = {locale: 'en_US', channel: 'ecommerce'};

const assetFamily = {
  identifier: 'packshot',
  labels: {
    en_US: 'Packshot',
  },
  attributeAsMainMedia: 'image_attribute_identifier',
  attributes: [imageAttribute],
};

const dataProvider = {
  assetFamilyFetcher: {
    fetch: () =>
      Promise.resolve({
        assetFamily,
        permission: {
          assetFamilyIdentifier: 'packshot',
          edit: true,
        },
      }),
  },
  channelFetcher: {
    fetchAll: () =>
      Promise.resolve([
        {
          code: 'ecommerce',
          locales: [],
        },
      ]),
  },
};

test('It renders a mass uploader button and cancel it', async () => {
  renderWithProviders(
    <MassUploader
      assetFamilyIdentifier={'packshot'}
      context={context}
      onAssetCreated={() => {}}
      dataProvider={dataProvider}
    />
  );

  fireEvent.click(await screen.findByText('pim_asset_manager.asset_collection.upload_asset'));

  expect(screen.getByText('pim_asset_manager.asset.upload.add_to_product')).toBeInTheDocument();

  fireEvent.click(await screen.findByTitle('pim_common.close'));

  expect(screen.queryByText('pim_asset_manager.asset.upload.confirm')).not.toBeInTheDocument();
});
