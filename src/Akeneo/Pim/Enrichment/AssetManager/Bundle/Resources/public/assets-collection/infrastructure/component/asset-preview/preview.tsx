import * as React from 'react';
import styled from 'styled-components';
import {
  Asset,
  getAssetLabel,
  getAssetMainImageDownloadLink,
  getAssetMainImageOriginalFilename,
  assetHasMainImage,
  getAttributeAsMainImage,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import __ from 'akeneoassetmanager/tools/translator';
import Download from 'akeneoassetmanager/application/component/app/icon/download';
import Link from 'akeneoassetmanager/application/component/app/icon/link';
import Edit from 'akeneoassetmanager/application/component/app/icon/edit';
import {
  MediaPreviewTypes,
  getAssetPreview,
  getAssetEditUrl,
  copyToClipboard,
} from 'akeneoassetmanager/tools/media-url-generator';
import {
  NormalizedMediaLinkAttribute,
  MEDIA_LINK_ATTRIBUTE_TYPE,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MediaTypes, YOUTUBE_EMBED_URL} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {IMAGE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/image';

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
};

const DownloadAction = ({asset, context}: PreviewProps) => (
  <Action
    href={getAssetMainImageDownloadLink(asset, context)}
    download={getAssetMainImageOriginalFilename(asset, context)}
    target="_blank"
  >
    <Download />
    <Label>{__('pim_asset_manager.asset_preview.download')}</Label>
  </Action>
);

const CopyUrlAction = ({asset, context}: PreviewProps) => (
  <Action onClick={() => copyToClipboard(getAssetMainImageDownloadLink(asset, context))}>
    <Link />
    <Label>{__('pim_asset_manager.asset_preview.copy_url')}</Label>
  </Action>
);

const EditAction = ({asset}: PreviewProps) => (
  <Action href={getAssetEditUrl(asset)} target="_blank">
    <Edit />
    <Label>{__('pim_asset_manager.asset_preview.edit_asset')}</Label>
  </Action>
);

const getBinaryPreviewView = (asset: Asset, context: Context) => (
  <>
    <PreviewImage asset={asset} context={context} />
    <Actions>
      <DownloadAction asset={asset} context={context} />
      <EditAction asset={asset} context={context} />
    </Actions>
  </>
);

const getMediaLinkPreviewView = (asset: Asset, context: Context) => {
  const mediaLinkAttribute = getAttributeAsMainImage(asset) as NormalizedMediaLinkAttribute;

  switch (mediaLinkAttribute.media_type) {
    case MediaTypes.youtube:
      return (
        <>
          <YouTubePlayer
            src={YOUTUBE_EMBED_URL + getAssetMainImageOriginalFilename(asset, context)}
            data-role="youtube-player"
          />
          <Actions>
            <CopyUrlAction asset={asset} context={context} />
            <EditAction asset={asset} context={context} />
          </Actions>
        </>
      );
    case MediaTypes.image:
    case MediaTypes.pdf:
    case MediaTypes.other:
      return (
        <>
          <PreviewImage asset={asset} context={context} />
          <Actions>
            <DownloadAction asset={asset} context={context} />
            <CopyUrlAction asset={asset} context={context} />
            <EditAction asset={asset} context={context} />
          </Actions>
        </>
      );
    default:
      throw Error(`The preview type ${mediaLinkAttribute.media_type} is not supported`);
  }
};

const PreviewImage = ({asset, context}: PreviewProps) => (
  <Image
    src={getAssetPreview(asset, MediaPreviewTypes.Preview)}
    alt={getAssetLabel(asset, context.locale)}
    data-role="asset-preview"
  />
);

const getPreviewView = (asset: Asset, context: Context) => {
  if (!assetHasMainImage(asset, context))
    return (
      <>
        <PreviewImage asset={asset} context={context} />
        <Message>{__('pim_asset_manager.asset_preview.empty_main_image')}</Message>
        <Actions>
          <EditAction asset={asset} context={context} />
        </Actions>
      </>
    );

  const attributeAsMainImage = getAttributeAsMainImage(asset);

  switch (attributeAsMainImage.type) {
    case MEDIA_LINK_ATTRIBUTE_TYPE:
      return getMediaLinkPreviewView(asset, context);
    case IMAGE_ATTRIBUTE_TYPE:
    default:
      return getBinaryPreviewView(asset, context);
  }
};

export const Preview = ({asset, context}: PreviewProps) => (
  <Container>
    <Border>{getPreviewView(asset, context)}</Border>
  </Container>
);
