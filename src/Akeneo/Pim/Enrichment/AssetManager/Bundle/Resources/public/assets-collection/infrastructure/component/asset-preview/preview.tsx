import * as React from 'react';
import styled from 'styled-components';
import {
  Asset,
  getAssetLabel,
  ImageData,
  getAssetMainImageDownloadLink,
  getAssetMainImageOriginalFilename,
  assetMainImageCanBeDownloaded,
  assetHasMainImage,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import __ from 'akeneoassetmanager/tools/translator';
import Download from 'akeneoassetmanager/application/component/app/icon/download';
import Fullscreen from 'akeneoassetmanager/application/component/app/icon/fullscreen';
import {MediaPreviewTypes, getAssetPreview, getMediaDownloadUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {NormalizedMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';

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

export const getMediaLinkUrl = (image: ImageData, attribute: NormalizedMediaLinkAttribute): string => {
  return `${null !== attribute.prefix ? attribute.prefix : ''}${image.filePath}${
    null !== attribute.suffix ? attribute.suffix : ''
  }`;
};

export const Preview = ({asset, context}: PreviewProps) => {
  return (
    <Container>
      <Border>
        <Image
          src={getAssetPreview(asset, MediaPreviewTypes.Preview)}
          alt={getAssetLabel(asset, context.locale)}
          data-role="asset-preview"
        />
        {assetHasMainImage(asset, context) ? (
          <Actions>
            {assetMainImageCanBeDownloaded(asset, context) ? (
              <Action
                href={getAssetMainImageDownloadLink(asset, context, getMediaLinkUrl, getMediaDownloadUrl)}
                download={getAssetMainImageOriginalFilename(asset, context)}
              >
                <Download />
                <Label>{__('pim_asset_manager.download')}</Label>
              </Action>
            ) : null}
            <Action
              href={getAssetMainImageDownloadLink(asset, context, getMediaLinkUrl, getMediaDownloadUrl)}
              target="_blank"
            >
              <Fullscreen />
              <Label>{__('pim_asset_manager.fullscreen')}</Label>
            </Action>
          </Actions>
        ) : null}
      </Border>
    </Container>
  );
};
