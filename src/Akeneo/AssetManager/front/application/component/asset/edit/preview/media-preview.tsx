import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import Download from 'akeneoassetmanager/application/component/app/icon/download';
import Link from 'akeneoassetmanager/application/component/app/icon/link';
import Edit from 'akeneoassetmanager/application/component/app/icon/edit';
import {
  copyToClipboard,
  canCopyToClipboard,
  getImageDownloadUrl,
  getMediaPreviewUrl,
} from 'akeneoassetmanager/tools/media-url-generator';
import {
  NormalizedMediaLinkAttribute,
  MEDIA_LINK_ATTRIBUTE_TYPE,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {
  NormalizedMediaFileAttribute,
  MEDIA_FILE_ATTRIBUTE_TYPE,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import MediaLinkData, {
  getYouTubeWatchUrl,
  getYouTubeEmbedUrl,
  getMediaLinkUrl,
  isMediaLinkData,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import MediaFileData, {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import useImageLoader from 'akeneoassetmanager/application/hooks/image-loader';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {MediaPreviewType, emptyMediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import Data, {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import ErrorBoundary from 'akeneoassetmanager/application/component/app/error-boundary';
import {PreviewModel} from 'akeneoassetmanager/domain/model/asset/value';

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

const DownloadAction = ({url, fileName}: {url: string; fileName: string}) => (
  <Action href={url} download={fileName} target="_blank">
    <Download />
    <Label>{__('pim_asset_manager.asset_preview.download')}</Label>
  </Action>
);

const CopyUrlAction = ({url}: {url: string}) =>
  /* istanbul ignore next */
  url && canCopyToClipboard() ? (
    <Action title={__('pim_asset_manager.asset_preview.copy_url')} onClick={() => copyToClipboard(url)}>
      <Link />
      <Label>{__('pim_asset_manager.asset_preview.copy_url')}</Label>
    </Action>
  ) : null;

const EditAction = ({url}: {url?: string}) =>
  url ? (
    <Action href={url} target="_blank">
      <Edit />
      <Label>{__('pim_asset_manager.asset_preview.edit_asset')}</Label>
    </Action>
  ) : null;

const LazyLoadedImage = React.memo(({src, alt, ...props}: {src: string; alt: string}) => {
  const loadedSrc = useImageLoader(src);

  return <Image src={loadedSrc} alt={alt} {...props} />;
});

const getMediaDataPreviewUrl = (data: Data, attributeAsMainMedia?: NormalizedAttribute): string => {
  if (!attributeAsMainMedia) return getMediaPreviewUrl(emptyMediaPreview());

  return getMediaPreviewUrl({
    type: MediaPreviewType.Preview,
    attributeIdentifier: attributeAsMainMedia.identifier,
    data: getMediaData(data),
  });
};

const MediaFilePreview = ({
  label,
  editUrl,
  mediaFileData,
  attribute,
}: {
  label: string;
  editUrl?: string;
  mediaFileData: MediaFileData;
  attribute: NormalizedMediaFileAttribute;
}) => {
  /* istanbul ignore next */
  if (null === mediaFileData) throw Error('The mediaFileData should not be empty at this point');

  return (
    <>
      <LazyLoadedImage
        src={getMediaDataPreviewUrl(mediaFileData, attribute)}
        alt={label}
        data-role="media-file-preview"
      />
      {attribute.media_type === MediaTypes.other && (
        <Message title={__('pim_asset_manager.asset_preview.other_main_media')}>
          {__('pim_asset_manager.asset_preview.other_main_media')}
        </Message>
      )}
      <Actions>
        <DownloadAction url={getImageDownloadUrl(mediaFileData)} fileName={mediaFileData.originalFilename} />
        <EditAction url={editUrl} />
      </Actions>
    </>
  );
};

const MediaLinkPreview = ({
  label,
  editUrl,
  mediaLinkData,
  attribute,
}: {
  label: string;
  editUrl?: string;
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
          <LazyLoadedImage
            src={getMediaDataPreviewUrl(mediaLinkData, attribute)}
            alt={label}
            data-role="media-link-preview"
          />
          {attribute.media_type === MediaTypes.other && (
            <Message title={__('pim_asset_manager.asset_preview.other_main_media')}>
              {__('pim_asset_manager.asset_preview.other_main_media')}
            </Message>
          )}
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

export const EmptyMediaPreview = ({label, editUrl}: {label: string; editUrl?: string}) => (
  <>
    <LazyLoadedImage src={getMediaDataPreviewUrl(null)} alt={label} data-role="empty-preview" />
    <Message title={__('pim_asset_manager.asset_preview.empty_main_media')}>
      {__('pim_asset_manager.asset_preview.empty_main_media')}
    </Message>
    <Actions>
      <EditAction url={editUrl} />
    </Actions>
  </>
);

type MediaPreviewProps = {
  previewModel: PreviewModel | undefined;
  label: string;
  editUrl?: string;
  attribute: NormalizedAttribute;
};

const Preview = ({previewModel, label, editUrl, attribute}: MediaPreviewProps) => {
  if (undefined === previewModel || null === previewModel.data)
    return <EmptyMediaPreview label={label} editUrl={editUrl} />;

  switch (attribute.type) {
    case MEDIA_LINK_ATTRIBUTE_TYPE:
      if (!isMediaLinkData(previewModel.data)) throw Error('The media link data is not valid');

      return (
        <MediaLinkPreview
          label={label}
          editUrl={editUrl}
          mediaLinkData={previewModel.data}
          attribute={attribute as NormalizedMediaLinkAttribute}
        />
      );
    case MEDIA_FILE_ATTRIBUTE_TYPE:
    default:
      if (!isMediaFileData(previewModel.data)) throw Error('The media file data is not valid');

      return (
        <MediaFilePreview
          label={label}
          editUrl={editUrl}
          mediaFileData={previewModel.data}
          attribute={attribute as NormalizedMediaFileAttribute}
        />
      );
  }
};

export const MediaPreview = ({previewModel, label, editUrl, attribute}: MediaPreviewProps) => (
  <Container>
    <Border>
      <ErrorBoundary errorMessage={__('pim_asset_manager.asset_preview.error')}>
        <Preview previewModel={previewModel} label={label} editUrl={editUrl} attribute={attribute} />
      </ErrorBoundary>
    </Border>
  </Container>
);
