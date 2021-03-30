import React from 'react';
import {screen} from '@testing-library/react';
import {MainMediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/main-media-preview';
import {renderWithAssetManagerProviders} from '../../../../tools';

const mediaLinkAttribute = {
  identifier: 'front_view',
  asset_family_identifier: 'designer',
  code: 'front_view',
  labels: {en_US: 'Front view'},
  type: 'media_link',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  media_type: 'image',
};
const assetFamily = {
  attributeAsMainMedia: 'front_view',
  attributes: [mediaLinkAttribute],
};
const mediaLinkData = 'some.jpg';
const mediaLinkValue = {
  attribute: mediaLinkAttribute,
  channel: 'ecommerce',
  locale: 'en_US',
  data: mediaLinkData,
};
const editionAsset = {
  code: 'my_asset',
  assetFamily,
  values: [mediaLinkValue],
  labels: {en_US: 'nice label'},
};
const context = {
  channel: 'ecommerce',
  locale: 'en_US',
};

test('It renders a main media preview', () => {
  renderWithAssetManagerProviders(<MainMediaPreview asset={editionAsset} context={context} />);

  expect(screen.getByTitle('pim_asset_manager.attribute.media_link.reload')).toBeInTheDocument();
  expect(screen.getByTitle('pim_asset_manager.asset_preview.download')).toBeInTheDocument();
  expect(screen.getByTitle('pim_asset_manager.asset.button.fullscreen')).toBeInTheDocument();
});
