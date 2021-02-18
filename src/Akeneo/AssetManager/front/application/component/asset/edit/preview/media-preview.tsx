import React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
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
  getYouTubeEmbedUrl,
  isMediaLinkData,
  getVimeoEmbedUrl,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import useImageLoader from 'akeneoassetmanager/application/hooks/image-loader';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {MediaPreviewType, emptyMediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {getMediaData, MediaData, isDataEmpty} from 'akeneoassetmanager/domain/model/asset/data';
import ErrorBoundary from 'akeneoassetmanager/application/component/app/error-boundary';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';
import {connect} from 'react-redux';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';

const Image = styled.img`
  width: auto;
  object-fit: contain;
  min-height: 250px;
  max-width: 100%;
  max-height: calc(100vh - 250px);
`;

const Message = styled.div`
  text-align: center;
`;

const EmbedPlayer = styled.iframe`
  width: 640px;
  height: 360px;
  border: none;
`;

const ImagePlaceholder = styled.div`
  width: 400px;
  height: 300px;
`;

type LazyLoadedImageProps = {
  src: string;
  alt: string;
  isLoading?: boolean;
};

const LazyLoadedImage = ({src, alt, isLoading = false, ...props}: LazyLoadedImageProps) => {
  const loadedSrc = useImageLoader(src);

  return undefined === loadedSrc || isLoading ? (
    <div className="AknLoadingPlaceHolderContainer">
      <ImagePlaceholder title={alt} {...props} />
    </div>
  ) : (
    <Image src={loadedSrc} alt={alt} {...props} />
  );
};

const DisconnectedMediaDataPreview = ({
  label,
  mediaData,
  attribute,
  reloadPreview,
}: {
  label: string;
  mediaData: MediaData;
  attribute: NormalizedMediaFileAttribute | NormalizedMediaLinkAttribute;
  reloadPreview: boolean;
}) => {
  const url = getMediaPreviewUrl({
    type: MediaPreviewType.Preview,
    attributeIdentifier: attribute.identifier,
    data: getMediaData(mediaData),
  });
  const [regenerate, doRegenerate, refreshedUrl] = useRegenerate(url);
  const [previewError, setPreviewError] = React.useState(false);

  React.useEffect(() => {
    if (reloadPreview) {
      doRegenerate();
    }
  }, [reloadPreview]);

  const handlePreviewError = (event: React.SyntheticEvent<HTMLImageElement, Event>) => {
    const emptyMediaUrl = getMediaPreviewUrl(emptyMediaPreview());
    (event.target as HTMLInputElement).setAttribute('src', emptyMediaUrl);
    setPreviewError(true);
  };

  return (
    <>
      {regenerate ? (
        <div className="AknLoadingPlaceHolderContainer">
          <ImagePlaceholder title={label} />
        </div>
      ) : (
        <Image src={refreshedUrl} alt={label} onError={handlePreviewError} />
      )}
      {(previewError || attribute.media_type === MediaTypes.other) && (
        <Message title={__('pim_asset_manager.asset_preview.other_main_media')}>
          {__('pim_asset_manager.asset_preview.other_main_media')}
        </Message>
      )}
    </>
  );
};

const MediaDataPreview = connect((state: EditState) => ({
  reloadPreview: state.reloadPreview,
}))(DisconnectedMediaDataPreview);

const MediaLinkPreview = ({
  label,
  mediaLinkData,
  attribute,
}: {
  label: string;
  mediaLinkData: MediaLinkData;
  attribute: NormalizedMediaLinkAttribute;
}) => {
  switch (attribute.media_type) {
    case MediaTypes.youtube:
      return <EmbedPlayer title={label} src={getYouTubeEmbedUrl(mediaLinkData)} allowFullScreen />;
    case MediaTypes.vimeo:
      return <EmbedPlayer title={label} src={getVimeoEmbedUrl(mediaLinkData)} allowFullScreen />;
    case MediaTypes.image:
    case MediaTypes.pdf:
    case MediaTypes.other:
      return <MediaDataPreview label={label} mediaData={mediaLinkData} attribute={attribute} />;
    default:
      throw Error(`The preview type ${attribute.media_type} is not supported`);
  }
};

export const EmptyMediaPreview = ({label = ''}: {label?: string}) => (
  <>
    <LazyLoadedImage src={getMediaPreviewUrl(emptyMediaPreview())} alt={label} />
    <Message title={__('pim_asset_manager.asset_preview.empty_main_media')}>
      {__('pim_asset_manager.asset_preview.empty_main_media')}
    </Message>
  </>
);

type MediaPreviewProps = {
  label: string;
  data: MediaData;
  attribute: NormalizedAttribute;
};

const Preview = ({data, label, attribute}: MediaPreviewProps) => {
  if (isDataEmpty(data)) return <EmptyMediaPreview label={label} />;

  switch (attribute.type) {
    case MEDIA_LINK_ATTRIBUTE_TYPE:
      if (!isMediaLinkData(data)) throw Error('The media link data is not valid');

      return (
        <MediaLinkPreview label={label} mediaLinkData={data} attribute={attribute as NormalizedMediaLinkAttribute} />
      );
    case MEDIA_FILE_ATTRIBUTE_TYPE:
    default:
      if (!isMediaFileData(data)) throw Error('The media file data is not valid');

      return <MediaDataPreview label={label} mediaData={data} attribute={attribute as NormalizedMediaFileAttribute} />;
  }
};

export const MediaPreview = ({data, label, attribute}: MediaPreviewProps) => (
  <ErrorBoundary errorMessage={__('pim_asset_manager.asset_preview.error')}>
    <Preview data={data} label={label} attribute={attribute} />
  </ErrorBoundary>
);
