import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {MediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/media-preview';
import {Provider} from 'react-redux';
import {createStore} from 'redux';

jest.mock('akeneoassetmanager/application/hooks/image-loader', () => {
  return jest.fn((url: string): string => {
    return url;
  })
})

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
const mediaLinkVimeoAttribute = {
  identifier: 'media_link_vimeo_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: MediaTypes.vimeo,
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
const otherData = {some: 'thing'};

describe('Tests media preview component', () => {
  test('It renders a empty media preview', () => {
    const {getByText, container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <MediaPreview data={null} label="" attribute={mediaFileAttribute} />
      </ThemeProvider>
    );

    expect(container.querySelector('[data-role="empty-preview"]')).toBeInTheDocument();
    expect(getByText('pim_asset_manager.asset_preview.empty_main_media')).toBeInTheDocument();
  });

  test('It renders a media file preview', () => {
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: false}))}>
          <MediaPreview data={mediaFileData} label="" attribute={mediaFileAttribute} />
        </Provider>
      </ThemeProvider>
    );

    expect(container.querySelector('[data-role="empty-preview"]')).toBeInTheDocument();
  });

  test('It renders a media file reloaded preview', () => {
    global.fetch = jest.fn().mockImplementation(() => new Promise(() => {}));

    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: true}))}>
          <MediaPreview data={mediaLinkData} label="" attribute={mediaLinkImageAttribute} />
        </Provider>
      </ThemeProvider>
    );

    expect(container.querySelector('.AknLoadingPlaceHolderContainer')).toBeInTheDocument();

    global.fetch.mockClear();
    delete global.fetch;
  });

  test('It renders a media link image preview', () => {
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: false}))}>
          <MediaPreview data={mediaLinkData} label="" attribute={mediaLinkImageAttribute} />
        </Provider>
      </ThemeProvider>
    );

    expect(container.querySelector('[data-role="empty-preview"]')).toBeInTheDocument();
  });

  test('It renders a media link youtube preview', () => {
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <MediaPreview data={mediaLinkData} label="" attribute={mediaLinkYouTubeAttribute} />
      </ThemeProvider>
    );

    expect(container.querySelector('[data-role="youtube-preview"]')).toBeInTheDocument();
  });

  test('It renders a media link vimeo preview', () => {
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <MediaPreview data={mediaLinkData} label="" attribute={mediaLinkVimeoAttribute} />
      </ThemeProvider>
    );

    expect(container.querySelector('[data-role="vimeo-preview"]')).toBeInTheDocument();
  });

  test('It tells when the provided media link media type is unknown', () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation(() => {});
    const {getByText} = render(
      <ThemeProvider theme={akeneoTheme}>
        <MediaPreview data={mediaLinkData} label="" attribute={mediaLinkUnknownAttribute} />
      </ThemeProvider>
    );

    expect(getByText('The preview type UNKNOWN is not supported')).toBeInTheDocument();
    mockedConsole.mockRestore();
  });

  test('It tells when the provided media link data is invalid', () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation(() => {});
    const {getByText} = render(
      <ThemeProvider theme={akeneoTheme}>
        <MediaPreview data={otherData} label="" attribute={mediaLinkImageAttribute} />
      </ThemeProvider>
    );

    expect(getByText('The media link data is not valid')).toBeInTheDocument();
    mockedConsole.mockRestore();
  });

  test('It tells when the provided media file data is invalid', () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation(() => {});
    const {getByText} = render(
      <ThemeProvider theme={akeneoTheme}>
        <MediaPreview data={otherData} label="" attribute={mediaFileAttribute} />
      </ThemeProvider>
    );

    expect(getByText('The media file data is not valid')).toBeInTheDocument();
    mockedConsole.mockRestore();
  });
});
