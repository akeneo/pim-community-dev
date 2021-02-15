import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {MediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/media-preview';
import {Provider} from 'react-redux';
import {createStore} from 'redux';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

const routing = require('routing');
jest.mock('routing');

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
    renderWithProviders(<MediaPreview data={null} label="" attribute={mediaFileAttribute} />);

    expect(screen.getByText('pim_asset_manager.asset_preview.empty_main_media')).toBeInTheDocument();
  });

  test('It renders a media preview impossible to generate', () => {
    routing.generate = jest
      .fn()
      .mockImplementation((route: string, parameters: any) => route + '?' + new URLSearchParams(parameters).toString());

    renderWithProviders(
      <Provider store={createStore(() => ({reloadPreview: false}))}>
        <MediaPreview data={mediaFileData} label="" attribute={mediaFileAttribute} />
      </Provider>
    );

    const previewImg = screen.getByRole('img');
    fireEvent(previewImg, new Event('error'));

    expect(previewImg).toHaveAttribute(
      'src',
      'akeneo_asset_manager_image_preview?type=thumbnail&attributeIdentifier=UNKNOWN&data='
    );
  });

  test('It renders a media file preview', () => {
    renderWithProviders(
      <Provider store={createStore(() => ({reloadPreview: false}))}>
        <MediaPreview data={mediaFileData} label="nice img" attribute={mediaFileAttribute} />
      </Provider>
    );

    expect(screen.getByRole('img')).toBeInTheDocument();
    expect(screen.getByAltText('nice img')).toBeInTheDocument();
  });

  test('It renders a media file reloaded preview', () => {
    global.fetch = jest.fn().mockImplementation(() => new Promise(() => {}));

    renderWithProviders(
      <Provider store={createStore(() => ({reloadPreview: true}))}>
        <MediaPreview data={mediaLinkData} label="loading" attribute={mediaLinkImageAttribute} />
      </Provider>
    );

    expect(screen.getByTitle('loading')).toBeInTheDocument();

    global.fetch.mockClear();
    delete global.fetch;
  });

  test('It renders a media link image preview', () => {
    renderWithProviders(
      <Provider store={createStore(() => ({reloadPreview: false}))}>
        <MediaPreview data={mediaLinkData} label="media link preview" attribute={mediaLinkImageAttribute} />
      </Provider>
    );

    expect(screen.getByAltText('media link preview')).toBeInTheDocument();
  });

  test('It renders a media link youtube preview', () => {
    renderWithProviders(<MediaPreview data={mediaLinkData} label="youtube" attribute={mediaLinkYouTubeAttribute} />);

    expect(screen.getByTitle('youtube')).toBeInTheDocument();
  });

  test('It renders a media link vimeo preview', () => {
    renderWithProviders(<MediaPreview data={mediaLinkData} label="vimeo" attribute={mediaLinkVimeoAttribute} />);

    expect(screen.getByTitle('vimeo')).toBeInTheDocument();
  });

  test('It tells when the provided media link media type is unknown', () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation(() => {});
    renderWithProviders(<MediaPreview data={mediaLinkData} label="" attribute={mediaLinkUnknownAttribute} />);

    expect(screen.getByText('The preview type UNKNOWN is not supported')).toBeInTheDocument();
    mockedConsole.mockRestore();
  });

  test('It tells when the provided media link data is invalid', () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation(() => {});
    renderWithProviders(<MediaPreview data={otherData} label="" attribute={mediaLinkImageAttribute} />);

    expect(screen.getByText('The media link data is not valid')).toBeInTheDocument();
    mockedConsole.mockRestore();
  });

  test('It tells when the provided media file data is invalid', () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation(() => {});
    renderWithProviders(<MediaPreview data={otherData} label="" attribute={mediaFileAttribute} />);

    expect(screen.getByText('The media file data is not valid')).toBeInTheDocument();
    mockedConsole.mockRestore();
  });
});
