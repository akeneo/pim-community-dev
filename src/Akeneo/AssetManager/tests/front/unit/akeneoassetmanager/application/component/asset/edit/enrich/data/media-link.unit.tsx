import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {view as MediaLinkView} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media-link';
import {ReloadAction} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {Provider} from 'react-redux';
import {createStore, applyMiddleware} from 'redux';
import thunkMiddleware from 'redux-thunk';

const mediaLinkImageAttribute = {
  code: 'mlimage',
  identifier: 'media_link_image_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: 'image',
  prefix: 'http://',
  suffix: '.png',
  labels: {},
};
const mediaLinkData = 'pim';
const locale = null;
const channel = null;
const mediaLinkValue = {
  attribute: mediaLinkImageAttribute,
  channel,
  locale,
  data: mediaLinkData,
};

const mediaFileImageAttribute = {
  code: 'mfimage',
  identifier: 'media_file_image_attribute_identifier',
  type: MEDIA_FILE_ATTRIBUTE_TYPE,
  media_type: 'image',
  prefix: 'http://',
  suffix: '.png',
  labels: {},
};
const mediaFileValue = {
  attribute: mediaLinkImageAttribute,
  channel,
  locale,
  data: {
    originalFilename: 'pim',
    filePath: 'pim.png',
  },
};

describe('Tests media link attribute component', () => {
  test('It renders the media link attribute with its preview and its actions', () => {
    const {container, getByTitle, getAllByTitle, getByAltText} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: false}))}>
          <MediaLinkView
            value={mediaLinkValue}
            locale={locale}
            onChange={() => {}}
            onSubmit={() => {}}
            canEditData={true}
          />
        </Provider>
      </ThemeProvider>
    );

    const inputElement = container.querySelector<HTMLInputElement>(
      '[placeholder="pim_asset_manager.attribute.media_link.placeholder"]'
    );
    expect(inputElement).toBeInTheDocument();
    expect(inputElement.value).toEqual('pim');
    expect(getAllByTitle('pim_asset_manager.asset_preview.download')[0]).toBeInTheDocument();
    expect(getByTitle('pim_asset_manager.asset.button.fullscreen')).toBeInTheDocument();
    expect(getByAltText('pim_asset_manager.attribute.media_type_preview')).toBeInTheDocument();
  });

  test('It renders the media link attribute with its reloaded preview', () => {
    global.fetch = jest.fn().mockImplementation(() => new Promise(() => {}));

    const {container, getByTitle, getAllByTitle, getByAltText} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: true}), applyMiddleware(thunkMiddleware))}>
          <MediaLinkView
            value={mediaLinkValue}
            locale={locale}
            onChange={() => {}}
            onSubmit={() => {}}
            canEditData={true}
          />
        </Provider>
      </ThemeProvider>
    );

    const loadingElement = container.querySelector<HTMLImageElement>('.AknLoadingPlaceHolder');
    expect(loadingElement).toBeInTheDocument();
    expect(getAllByTitle('pim_asset_manager.asset_preview.download')[0]).toBeInTheDocument();
    expect(getByTitle('pim_asset_manager.asset.button.fullscreen')).toBeInTheDocument();
    fireEvent.click(getByTitle('pim_asset_manager.attribute.media_link.reload'));

    global.fetch.mockClear();
    delete global.fetch;
  });

  test('It does not render a reload action if it is not a media link', () => {
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <ReloadAction
          data={mediaFileValue.data}
          onReload={() => {}}
          attribute={mediaFileValue.attribute}
          label={'pim_asset_manager.attribute.media_link.reload'}
        />
      </ThemeProvider>
    );

    expect(container.querySelector('[title="pim_asset_manager.attribute.media_link.reload"]')).toBe(null);
  });

  test('It renders the an empty preview and the placeholder when the value is empty', () => {
    const emptyValue = {...mediaLinkValue, data: null};
    const {container, getByAltText} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: false}))}>
          <MediaLinkView
            value={emptyValue}
            locale={locale}
            onChange={() => {}}
            onSubmit={() => {}}
            canEditData={true}
          />
        </Provider>
      </ThemeProvider>
    );

    expect(container.querySelector('a[title="pim_asset_manager.asset_preview.download"]')).not.toBeInTheDocument();
    expect(container.querySelector('*[title="pim_asset_manager.asset.button.fullscreen"]')).not.toBeInTheDocument();
    expect(
      container.querySelector('[placeholder="pim_asset_manager.attribute.media_link.placeholder"]')
    ).toBeInTheDocument();
    expect(getByAltText('pim_asset_manager.attribute.media_type_preview')).toBeInTheDocument();
  });

  test('It does not render if the data is not a media link data', () => {
    const otherValue = {...mediaLinkValue, data: {some: 'thing'}};
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: false}))}>
          <MediaLinkView
            value={otherValue}
            locale={locale}
            onChange={() => {}}
            onSubmit={() => {}}
            canEditData={true}
          />
        </Provider>
      </ThemeProvider>
    );

    expect(container.querySelector('a[title="pim_asset_manager.asset_preview.download"]')).not.toBeInTheDocument();
    expect(container.querySelector('*[title="pim_asset_manager.asset.button.fullscreen"]')).not.toBeInTheDocument();
    expect(
      container.querySelector('[placeholder="pim_asset_manager.attribute.media_link.placeholder"]')
    ).not.toBeInTheDocument();
  });

  test('It can change the media link value', () => {
    let editionValue = mediaLinkValue;
    const change = jest.fn().mockImplementationOnce(value => (editionValue = value));
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: false}))}>
          <MediaLinkView
            value={editionValue}
            locale={locale}
            onChange={change}
            onSubmit={() => {}}
            canEditData={true}
          />
        </Provider>
      </ThemeProvider>
    );

    const inputElement = container.querySelector<HTMLInputElement>(
      '[placeholder="pim_asset_manager.attribute.media_link.placeholder"]'
    );

    fireEvent.change(inputElement, {target: {value: 'pam'}});
    expect(editionValue.data).toEqual('pam');
    expect(change).toHaveBeenCalledTimes(1);
  });

  test('It can submit the media link value by hitting the Enter key', () => {
    const submit = jest.fn().mockImplementationOnce(() => {});
    const {container} = render(
      <ThemeProvider theme={akeneoTheme}>
        <Provider store={createStore(() => ({reloadPreview: false}))}>
          <MediaLinkView
            value={mediaLinkValue}
            locale={locale}
            onChange={() => {}}
            onSubmit={submit}
            canEditData={true}
          />
        </Provider>
      </ThemeProvider>
    );

    const inputElement = container.querySelector<HTMLInputElement>(
      '[placeholder="pim_asset_manager.attribute.media_link.placeholder"]'
    );

    fireEvent.keyDown(inputElement, {key: 'Enter', code: 13});
    expect(submit).toHaveBeenCalledTimes(1);
  });
});
