import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {view as MediaLinkView} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media-link';
import {ReloadAction} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {ReloadPreviewProvider} from 'akeneoassetmanager/application/hooks/useReloadPreview';
import {renderWithAssetManagerProviders} from '../../../../../../tools';

const mediaLinkImageAttribute = {
  code: 'mlimage',
  identifier: 'media_link_image_attribute_identifier',
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  media_type: 'image',
  prefix: 'http://',
  suffix: '.png',
  labels: {},
};
const locale = null;
const channel = null;
const mediaLinkValue = {
  attribute: mediaLinkImageAttribute,
  channel,
  locale,
  data: 'pim',
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
    renderWithAssetManagerProviders(
      <MediaLinkView
        value={mediaLinkValue}
        locale={locale}
        onChange={() => {}}
        onSubmit={() => {}}
        canEditData={true}
      />
    );

    const inputElement = screen.getByPlaceholderText(
      'pim_asset_manager.attribute.media_link.placeholder'
    ) as HTMLInputElement;

    expect(inputElement).toBeInTheDocument();
    expect(inputElement.value).toEqual('pim');
    expect(screen.getAllByTitle('pim_asset_manager.asset_preview.download')[0]).toBeInTheDocument();
    expect(screen.getByTitle('pim_asset_manager.asset.button.fullscreen')).toBeInTheDocument();
    expect(screen.getByAltText('pim')).toBeInTheDocument();
  });

  test('It copies the media link value when clicking on the copy button', () => {
    Object.assign(navigator, {
      clipboard: {
        writeText: jest.fn(),
      },
    });

    renderWithAssetManagerProviders(
      <MediaLinkView
        value={mediaLinkValue}
        locale={locale}
        onChange={() => {}}
        onSubmit={() => {}}
        canEditData={true}
      />
    );

    fireEvent.click(screen.getByTitle('pim_asset_manager.asset_preview.copy_url'));
    expect(navigator.clipboard.writeText).toHaveBeenCalledWith('http://pim.png');
  });

  test('It renders the media link attribute with its reloaded preview', () => {
    global.fetch = jest.fn().mockImplementation(() => new Promise(() => {}));

    renderWithAssetManagerProviders(
      <ReloadPreviewProvider initialValue={true}>
        <MediaLinkView
          value={mediaLinkValue}
          locale={locale}
          onChange={() => {}}
          onSubmit={() => {}}
          canEditData={true}
        />
      </ReloadPreviewProvider>
    );

    expect(screen.getAllByTitle('pim_asset_manager.asset_preview.download')[0]).toBeInTheDocument();
    expect(screen.getByTitle('pim_asset_manager.asset.button.fullscreen')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_asset_manager.attribute.media_link.reload'));

    global.fetch.mockClear();
    delete global.fetch;
  });

  test('It does not render a reload action if it is not a media link', () => {
    renderWithAssetManagerProviders(
      <ReloadAction
        data={mediaFileValue.data}
        onReload={() => {}}
        attribute={mediaFileValue.attribute}
        label={'pim_asset_manager.attribute.media_link.reload'}
      />
    );

    expect(screen.queryByTitle('pim_asset_manager.attribute.media_link.reload')).toBe(null);
  });

  test('It renders the an empty preview and the placeholder when the value is empty', () => {
    const emptyValue = {...mediaLinkValue, data: null};
    renderWithAssetManagerProviders(
      <MediaLinkView value={emptyValue} locale={locale} onChange={() => {}} onSubmit={() => {}} canEditData={true} />
    );

    expect(screen.queryByTitle('pim_asset_manager.asset_preview.download')).not.toBeInTheDocument();
    expect(screen.queryByTitle('pim_asset_manager.asset.button.fullscreen')).not.toBeInTheDocument();
    expect(screen.getByPlaceholderText('pim_asset_manager.attribute.media_link.placeholder')).toBeInTheDocument();
  });

  test('It does not render if the data is not a media link data', () => {
    const otherValue = {...mediaLinkValue, data: {some: 'thing'}};
    renderWithAssetManagerProviders(
      <MediaLinkView value={otherValue} locale={locale} onChange={() => {}} onSubmit={() => {}} canEditData={true} />
    );

    expect(screen.queryByTitle('pim_asset_manager.asset_preview.download')).not.toBeInTheDocument();
    expect(screen.queryByTitle('pim_asset_manager.asset.button.fullscreen')).not.toBeInTheDocument();
    expect(screen.queryByPlaceholderText('pim_asset_manager.attribute.media_link.placeholder')).not.toBeInTheDocument();
  });

  test('It can change the media link value', () => {
    let editionValue = mediaLinkValue;
    const change = jest.fn().mockImplementationOnce(value => (editionValue = value));
    renderWithAssetManagerProviders(
      <MediaLinkView value={editionValue} locale={locale} onChange={change} onSubmit={() => {}} canEditData={true} />
    );

    const inputElement = screen.getByPlaceholderText(
      'pim_asset_manager.attribute.media_link.placeholder'
    ) as HTMLInputElement;

    fireEvent.change(inputElement, {target: {value: 'pam'}});
    expect(editionValue.data).toEqual('pam');
    expect(change).toHaveBeenCalledTimes(1);
  });

  test('It can submit the media link value by hitting the Enter key', () => {
    const submit = jest.fn().mockImplementationOnce(() => {});
    renderWithAssetManagerProviders(
      <MediaLinkView value={mediaLinkValue} locale={locale} onChange={() => {}} onSubmit={submit} canEditData={true} />
    );

    const inputElement = screen.getByPlaceholderText(
      'pim_asset_manager.attribute.media_link.placeholder'
    ) as HTMLInputElement;

    fireEvent.keyDown(inputElement, {key: 'Enter', code: 13});
    expect(submit).toHaveBeenCalledTimes(1);
  });
});
