import * as React from 'react';
import styled from 'styled-components';
import {Asset, getAssetLabel} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import __ from 'akeneoassetmanager/tools/translator';
import Download from 'akeneoassetmanager/application/component/app/icon/download';
import Link from 'akeneoassetmanager/application/component/app/icon/link';
import Edit from 'akeneoassetmanager/application/component/app/icon/edit';
import {
  copyToClipboard,
  canCopyToClipboard,
  getAssetPreview,
  getAssetEditUrlLegacy,
} from 'akeneoassetmanager/tools/media-url-generator';
import {
  NormalizedMediaLinkAttribute,
  MEDIA_LINK_ATTRIBUTE_TYPE,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {isMainMediaEmpty} from 'akeneoassetmanager/domain/model/asset/list-asset';
import MediaLinkData, {
  getYouTubeWatchUrl,
  getYouTubeEmbedUrl,
  getMediaLinkUrl,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {getPreviewModel} from 'akeneoassetmanager/domain/model/asset/list-value';
const routing = require('routing');

const Container = styled.div`
  display: flex;
  justify-content: center;
  align-items: center;
  flex: 1;
`;

const Border = styled.div`
  display: flex;
  flex-direction: column;
  padding: 20px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
`;

const Image = styled.img`
  width: auto;
  object-fit: contain;
  max-height: calc(100vh - 480px);
  min-height: 300px;
  max-width: 100%;
`;

const Actions = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  padding-top: 20px;
`;

const Action = styled.a`
  display: flex;
  align-items: center;

  &:not(:first-child) {
    margin-left: 20px;
  }

  &:hover {
    cursor: pointer;
  }
`;

const Message = styled.div`
  text-align: center;
`;

const Label = styled.span`
  margin-left: 5px;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey100};
`;

const YouTubePlayer = styled.iframe`
  width: 580px;
  height: 300px;
  border: none;
`;

type PreviewProps = {
  asset: Asset;
  context: Context;
  attributeAsMainMedia: NormalizedAttribute;
};

const DownloadAction = ({url, fileName}: {url: string; fileName: string}) => (
  <Action href={url} download={fileName} target="_blank">
    <Download />
    <Label>{__('pim_asset_manager.asset_preview.download')}</Label>
  </Action>
);

const CopyUrlAction = ({url}: {url: string}) =>
  canCopyToClipboard() ? (
    <Action onClick={() => copyToClipboard(url)}>
      <Link />
      <Label>{__('pim_asset_manager.asset_preview.copy_url')}</Label>
    </Action>
  ) : null;

const EditAction = ({url}: {url: string}) => (
  <Action href={url} target="_blank">
    <Edit />
    <Label>{__('pim_asset_manager.asset_preview.edit_asset')}</Label>
  </Action>
);

const getBinaryPreviewView = ({asset, context, attributeAsMainMedia}: PreviewProps) => (
  <>
    <PreviewImage asset={asset} context={context} attributeAsMainMedia={attributeAsMainMedia} />
    <Actions>
      <DownloadAction url={getAssetEditUrlLegacy(asset)} fileName={getAssetEditUrlLegacy(asset)} />
      <EditAction url={getAssetEditUrlLegacy(asset)} />
    </Actions>
  </>
);

//TODO clean
const getAssetEditUrl = (assetCode: AssetCode, assetFamilyIdentifier: AssetFamilyIdentifier): string =>
  '#' + routing.generate('akeneo_asset_manager_asset_edit', {assetFamilyIdentifier, assetCode, tab: 'enrich'});

const getMediaLinkPreviewView = (
  asset: Asset,
  mediaLinkData: MediaLinkData,
  context: Context,
  attribute: NormalizedMediaLinkAttribute
) => {
  const editUrl = getAssetEditUrl(asset.code, asset.assetFamily.identifier);
  switch (attribute.media_type) {
    case MediaTypes.youtube:
      return (
        <>
          <YouTubePlayer src={getYouTubeEmbedUrl(mediaLinkData)} data-role="youtube-player" />
          <Actions>
            <CopyUrlAction url={getYouTubeWatchUrl(mediaLinkData)} />
            <EditAction url={editUrl} />
          </Actions>
        </>
      );
    case MediaTypes.image:
    case MediaTypes.pdf:
    case MediaTypes.other:
      const url = getMediaLinkUrl(mediaLinkData, attribute);
      return (
        <>
          <PreviewImage asset={asset} context={context} attributeAsMainMedia={attribute} />
          <Actions>
            <DownloadAction url={url} fileName={url} />
            <CopyUrlAction url={url} />
            <EditAction url={editUrl} />
          </Actions>
        </>
      );
    default:
      throw Error(`The preview type ${attribute.media_type} is not supported`);
  }
};

const PreviewImage = ({asset, context}: PreviewProps) => (
  <Image
    src={getAssetPreview(asset, MediaPreviewType.Preview, context)}
    alt={getAssetLabel(asset, context.locale)}
    data-role="asset-preview"
  />
);

const getPreviewView = ({asset, context, attributeAsMainMedia}: PreviewProps) => {
  if (isMainMediaEmpty(asset, context.channel, context.locale))
    return (
      <>
        <PreviewImage asset={asset} context={context} attributeAsMainMedia={attributeAsMainMedia} />
        <Message>{__('pim_asset_manager.asset_preview.empty_main_media')}</Message>
        <Actions>
          <EditAction url={getAssetEditUrl(asset.code, asset.assetFamily.identifier)} />
        </Actions>
      </>
    );

  switch (attributeAsMainMedia.type) {
    case MEDIA_LINK_ATTRIBUTE_TYPE:
      const previewModel = getPreviewModel(asset.image, context.channel, context.locale)?.data as MediaLinkData;
      return getMediaLinkPreviewView(
        asset,
        previewModel,
        context,
        attributeAsMainMedia as NormalizedMediaLinkAttribute
      );
    case MEDIA_FILE_ATTRIBUTE_TYPE:
    default:
      return getBinaryPreviewView({asset, context, attributeAsMainMedia});
  }
};

export const Preview = ({asset, context, attributeAsMainMedia}: PreviewProps) => (
  <Container>
    <Border>{getPreviewView({asset, context, attributeAsMainMedia})}</Border>
  </Container>
);
