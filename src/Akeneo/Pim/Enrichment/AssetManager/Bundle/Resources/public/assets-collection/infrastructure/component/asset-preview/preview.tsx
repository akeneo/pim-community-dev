import * as React from 'react';
import styled from 'styled-components';
import {
  Asset,
  getAssetLabel,
  ImageData,
  getAssetMainImageDownloadLink,
  getAssetMainImageOriginalFilename,
  assetHasMainImage,
  getAttributeAsMainImage,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import __ from 'akeneoassetmanager/tools/translator';
import Download from 'akeneoassetmanager/application/component/app/icon/download';
import Fullscreen from 'akeneoassetmanager/application/component/app/icon/fullscreen';
import {MediaPreviewTypes, getAssetPreview, getMediaDownloadUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {
  NormalizedMediaLinkAttribute,
  MEDIA_LINK_ATTRIBUTE_TYPE,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
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
  max-height: calc(100vh - 450px);
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

const Label = styled.span`
  margin-left: 5px;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey100};
  text-transform: capitalize;
`;

type PreviewProps = {
  asset: Asset;
  context: Context;
};

export const getMediaLinkUrl = (image: ImageData, attribute: NormalizedMediaLinkAttribute): string =>
  `${null !== attribute.prefix ? attribute.prefix : ''}${image.filePath}${
    null !== attribute.suffix ? attribute.suffix : ''
  }`;

const FullscreenAction = ({asset, context}: PreviewProps) => (
  <Action href={getAssetMainImageDownloadLink(asset, context, getMediaLinkUrl, getMediaDownloadUrl)} target="_blank">
    <Fullscreen />
    <Label>{__('pim_asset_manager.fullscreen')}</Label>
  </Action>
);

const DownloadAction = ({asset, context}: PreviewProps) => (
  <Action
    href={getAssetMainImageDownloadLink(asset, context, getMediaLinkUrl, getMediaDownloadUrl)}
    download={getAssetMainImageOriginalFilename(asset, context)}
  >
    <Download />
    <Label>{__('pim_asset_manager.download')}</Label>
  </Action>
);

const getBinaryPreviewView = (asset: Asset, context: Context) => (
  <>
    <Image
      src={getAssetPreview(asset, MediaPreviewTypes.Preview)}
      alt={getAssetLabel(asset, context.locale)}
      data-role="asset-preview"
    />
    {assetHasMainImage(asset, context) && (
      <Actions>
        <DownloadAction asset={asset} context={context} />
        <FullscreenAction asset={asset} context={context} />
      </Actions>
    )}
  </>
);

const getMediaLinkPreviewView = (asset: Asset, context: Context) => {
  const attributeAsMainImage = getAttributeAsMainImage(asset) as NormalizedMediaLinkAttribute;
  switch (attributeAsMainImage.media_type) {
    case MediaTypes.image:
    case MediaTypes.youtube:
    case MediaTypes.other:
      return (
        <>
          <Image
            src={getAssetPreview(asset, MediaPreviewTypes.Preview)}
            alt={getAssetLabel(asset, context.locale)}
            data-role="asset-preview"
          />
          {assetHasMainImage(asset, context) && (
            <Actions>
              <FullscreenAction asset={asset} context={context} />
            </Actions>
          )}
        </>
      );
    default:
      throw Error(`The preview type ${attributeAsMainImage.media_type} is not supported`);
  }
};

const getPreviewView = (asset: Asset, context: Context) => {
  const attributeAsMainImage = getAttributeAsMainImage(asset);

  switch (attributeAsMainImage.type) {
    case MEDIA_LINK_ATTRIBUTE_TYPE:
      return getMediaLinkPreviewView(asset, context);
    case IMAGE_ATTRIBUTE_TYPE:
      return getBinaryPreviewView(asset, context);
    default:
      return (
        <>
          <Image
            src={getAssetPreview(asset, MediaPreviewTypes.Thumbnail)}
            alt={getAssetLabel(asset, context.locale)}
            data-role="asset-preview"
          />
          <Actions>
            <FullscreenAction asset={asset} context={context} />
          </Actions>
        </>
      );
  }
};

export const Preview = ({asset, context}: PreviewProps) => (
  <Container>
    <Border>{getPreviewView(asset, context)}</Border>
  </Container>
);
