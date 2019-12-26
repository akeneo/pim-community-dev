import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {Preview} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-preview/preview';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {mount} from 'enzyme';

const asset = {
  code: 'sideview',
  labels: {
    en_US: 'Sideview',
  },
  image: [],
  assetFamily: {
    identifier: 'assetFamilyIdentifier',
  },
};
const context = {locale: 'en_US', channel: 'ecommerce'};
const mediaLinkImageAttribute = {
  identifier: 'media_link_image_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: MediaTypes.image,
  prefix: 'http://',
  suffix: '.png',
};
const mediaLinkUnknownAttribute = {
  identifier: 'media_link_unknown_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: 'UNKNOWN',
};
const mediaLinkYouTubeAttribute = {
  identifier: 'media_link_youtube_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: MediaTypes.youtube,
};
const imageAttribute = {
  identifier: 'image_attribute_identifier',
  type: MEDIA_FILE_ATTRIBUTE_TYPE,
};

test('It renders a media link preview with no media', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Preview asset={asset} context={context} attributeAsMainMedia={mediaLinkImageAttribute} />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_preview.empty_main_media')).toBeInTheDocument();
});

test('It renders a media link preview with empty media', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Preview
        asset={{...asset, image: [{data: null, channel: null, locale: null}]}}
        context={context}
        attributeAsMainMedia={mediaLinkImageAttribute}
      />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_preview.empty_main_media')).toBeInTheDocument();
});

test('It renders a media link preview with invalid media', () => {
  jest.spyOn(console, 'error').mockImplementation(() => {});
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Preview
        asset={{
          ...asset,
          image: [{data: {originalFilename: 'nice', filePath: 'http://noice.com'}, channel: null, locale: null}],
        }}
        context={context}
        attributeAsMainMedia={mediaLinkImageAttribute}
      />
    </ThemeProvider>
  );
  expect(getByText('The media link data is not valid')).toBeInTheDocument();
  console.error.mockRestore();
});

test('It renders a media link preview with unsupported type', () => {
  jest.spyOn(console, 'error').mockImplementation(() => {});
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Preview
        asset={{
          ...asset,
          image: [{data: 'cool', channel: null, locale: null}],
        }}
        context={context}
        attributeAsMainMedia={mediaLinkUnknownAttribute}
      />
    </ThemeProvider>
  );
  expect(getByText('The preview type UNKNOWN is not supported')).toBeInTheDocument();
  console.error.mockRestore();
});

test('It renders a media link preview', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Preview
        asset={{
          ...asset,
          image: [{data: 'noice.com/wow', channel: null, locale: null}],
        }}
        context={context}
        attributeAsMainMedia={mediaLinkImageAttribute}
      />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_preview.edit_asset')).toBeInTheDocument();
});
test('It renders a youtube media link preview', () => {
  const {getByText, queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Preview
        asset={{
          ...asset,
          image: [{data: 'YOUTUBE_ID', channel: null, locale: null}],
        }}
        context={context}
        attributeAsMainMedia={mediaLinkYouTubeAttribute}
      />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_preview.edit_asset')).toBeInTheDocument();
  expect(queryByText('pim_asset_manager.asset_preview.download')).not.toBeInTheDocument();
});

test('It renders a media file preview with invalid media', () => {
  jest.spyOn(console, 'error').mockImplementation(() => {});
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Preview
        asset={{
          ...asset,
          image: [{data: 'nice.png', channel: null, locale: null}],
        }}
        context={context}
        attributeAsMainMedia={imageAttribute}
      />
    </ThemeProvider>
  );
  expect(getByText('The media file data is not valid')).toBeInTheDocument();
  console.error.mockRestore();
});

test('It renders a media file preview', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Preview
        asset={{
          ...asset,
          image: [{data: {originalFilename: 'nice', filePath: 'http://noice.com'}, channel: null, locale: null}],
        }}
        context={context}
        attributeAsMainMedia={imageAttribute}
      />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_preview.edit_asset')).toBeInTheDocument();
});
