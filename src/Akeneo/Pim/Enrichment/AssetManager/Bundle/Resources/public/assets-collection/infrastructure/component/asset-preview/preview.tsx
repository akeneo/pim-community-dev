import * as React from 'react';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import __ from 'akeneoassetmanager/tools/translator';
import Download from 'akeneoassetmanager/application/component/app/icon/download';
import Link from 'akeneoassetmanager/application/component/app/icon/link';
import Edit from 'akeneoassetmanager/application/component/app/icon/edit';
import {
  copyToClipboard,
  canCopyToClipboard,
  getMediaPreviewUrl,
  getImageDownloadUrl,
  getAssetEditUrl,
} from 'akeneoassetmanager/tools/media-url-generator';
import {
  NormalizedMediaLinkAttribute,
  MEDIA_LINK_ATTRIBUTE_TYPE,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {
  MEDIA_FILE_ATTRIBUTE_TYPE,
  NormalizedMediaFileAttribute,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import MediaLinkData, {
  getYouTubeWatchUrl,
  getYouTubeEmbedUrl,
  getMediaLinkUrl,
  isMediaLinkData,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import MediaFileData, {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {getPreviewModel} from 'akeneoassetmanager/domain/model/asset/list-value';
import {getLabel} from 'pimui/js/i18n';
import Data, {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';

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
  asset: ListAsset;
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
    <Action title={__('pim_asset_manager.asset_preview.copy_url')} onClick={() => copyToClipboard(url)}>
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

const getMediaDataPreviewUrl = (data: Data, attributeAsMainMedia: NormalizedAttribute): string =>
  getMediaPreviewUrl({
    type: MediaPreviewType.Preview,
    attributeIdentifier: attributeAsMainMedia.identifier,
    data: getMediaData(data),
  });

const MediaFilePreviewView = ({
  label,
  editUrl,
  mediaFileData,
  attribute,
}: {
  label: string;
  editUrl: string;
  mediaFileData: MediaFileData;
  attribute: NormalizedMediaFileAttribute;
}) => {
  if (null === mediaFileData) throw Error('The mediaFileData should not be empty at this point');

  return (
    <>
      <Image src={getMediaDataPreviewUrl(mediaFileData, attribute)} alt={label} data-role="asset-preview" />
      <Actions>
        <DownloadAction url={getImageDownloadUrl(mediaFileData)} fileName={mediaFileData.originalFilename} />
        <EditAction url={editUrl} />
      </Actions>
    </>
  );
};

const MediaLinkPreviewView = ({
  label,
  editUrl,
  mediaLinkData,
  attribute,
}: {
  label: string;
  editUrl: string;
  mediaLinkData: MediaLinkData;
  attribute: NormalizedMediaLinkAttribute;
}) => {
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
          <Image src={getMediaDataPreviewUrl(mediaLinkData, attribute)} alt={label} data-role="asset-preview" />
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

const PreviewView = ({
  asset,
  context,
  attributeAsMainMedia,
}: {
  asset: ListAsset;
  context: Context;
  attributeAsMainMedia: NormalizedAttribute;
}) => {
  const editUrl = getAssetEditUrl(asset);
  const label = getLabel(asset.labels, context.locale, asset.code);
  const previewModel = getPreviewModel(asset.image, context.channel, context.locale);

  if (undefined === previewModel || null === previewModel.data)
    return (
      <>
        <Image src={getMediaDataPreviewUrl('', attributeAsMainMedia)} alt={label} data-role="asset-preview" />
        <Message>{__('pim_asset_manager.asset_preview.empty_main_media')}</Message>
        <Actions>
          <EditAction url={editUrl} />
        </Actions>
      </>
    );

  switch (attributeAsMainMedia.type) {
    case MEDIA_LINK_ATTRIBUTE_TYPE:
      if (!isMediaLinkData(previewModel.data)) throw Error('The media link data is not valid');

      return (
        <MediaLinkPreviewView
          label={label}
          editUrl={editUrl}
          mediaLinkData={previewModel.data}
          attribute={attributeAsMainMedia as NormalizedMediaLinkAttribute}
        />
      );
    case MEDIA_FILE_ATTRIBUTE_TYPE:
    default:
      if (!isMediaFileData(previewModel.data)) throw Error('The media file data is not valid');

      return (
        <MediaFilePreviewView
          label={label}
          editUrl={editUrl}
          mediaFileData={previewModel.data}
          attribute={attributeAsMainMedia as NormalizedMediaFileAttribute}
        />
      );
  }
};

export const Preview = ({asset, context, attributeAsMainMedia}: PreviewProps) => (
  <Container>
    <Border>
      <PreviewView asset={asset} context={context} attributeAsMainMedia={attributeAsMainMedia} />
    </Border>
  </Container>
);
