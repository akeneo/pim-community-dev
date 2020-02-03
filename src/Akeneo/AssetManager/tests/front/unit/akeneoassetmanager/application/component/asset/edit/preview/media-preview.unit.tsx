import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {MediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/media-preview';

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
const mediaFileAttribute = {
  identifier: 'image_attribute_identifier',
  type: MEDIA_FILE_ATTRIBUTE_TYPE,
};
const mediaFileData = {
  originalFilename: 'fef3232.jpg',
  filePath: 'f/e/wq/fefwf.png',
};
const mediaLinkData = 'pim';
const mediaFilePreviewModel = {
  data: mediaFileData,
  channel: null,
  locale: null,
  attribute: mediaFileAttribute.identifier,
};
const mediaLinkPreviewModel = {
  data: mediaLinkData,
  channel: null,
  locale: null,
  attribute: mediaLinkImageAttribute.identifier,
};

test('It renders a empty media preview', () => {
  const emptyPreviewModel = {...mediaFilePreviewModel, data: null};
  const {getByText, container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MediaPreview previewModel={emptyPreviewModel} label="" attribute={mediaFileAttribute} />
    </ThemeProvider>
  );
  expect(container.querySelector('[data-role="empty-preview"]')).toBeInTheDocument();
  expect(getByText('pim_asset_manager.asset_preview.empty_main_media')).toBeInTheDocument();
});

test('It renders a media file preview with invalid media', () => {
  jest.spyOn(console, 'error').mockImplementation(() => {});
  const otherPreviewModel = {...mediaFilePreviewModel, data: {}};
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MediaPreview previewModel={otherPreviewModel} label="" attribute={mediaFileAttribute} />
    </ThemeProvider>
  );
  expect(getByText('The media file data is not valid')).toBeInTheDocument();
  console.error.mockRestore();
});

test('It renders a media file preview', () => {
  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MediaPreview previewModel={mediaFilePreviewModel} label="" editUrl="pim.com" attribute={mediaFileAttribute} />
    </ThemeProvider>
  );
  expect(container.querySelector('[data-role="media-file-preview"]')).toBeInTheDocument();
});

test('It renders a media link preview', () => {
  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MediaPreview
        previewModel={mediaLinkPreviewModel}
        label=""
        editUrl="pim.com"
        attribute={mediaLinkImageAttribute}
      />
    </ThemeProvider>
  );
  expect(container.querySelector('[data-role="media-link-preview"]')).toBeInTheDocument();
});

test('It tells when the provided media link media type is unknown', () => {
  jest.spyOn(console, 'error').mockImplementation(() => {});
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MediaPreview
        previewModel={mediaLinkPreviewModel}
        label=""
        editUrl="pim.com"
        attribute={mediaLinkUnknownAttribute}
      />
    </ThemeProvider>
  );
  expect(getByText('The preview type UNKNOWN is not supported')).toBeInTheDocument();
  console.error.mockRestore();
});

test('It tells when the provided media link data is invalid', () => {
  jest.spyOn(console, 'error').mockImplementation(() => {});
  const invalidPreviewModel = {...mediaLinkPreviewModel, data: {}};
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MediaPreview
        previewModel={invalidPreviewModel}
        label=""
        editUrl="pim.com"
        attribute={mediaLinkUnknownAttribute}
      />
    </ThemeProvider>
  );
  expect(getByText('The media link data is not valid')).toBeInTheDocument();
  console.error.mockRestore();
});
