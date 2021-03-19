import React, {useEffect} from 'react';
import {
  CopyIcon,
  DownloadIcon,
  FullscreenIcon,
  IconButton,
  MediaLinkInput,
  RefreshIcon,
  useBooleanState,
  useInModal
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {canCopyToClipboard, copyToClipboard, getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {
  canDownloadMediaLink,
  getMediaLinkUrl,
  isMediaLinkData,
  mediaLinkDataFromString,
  mediaLinkDataStringValue,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';
import {useReloadPreview} from 'akeneoassetmanager/application/hooks/useReloadPreview';
import {ViewGeneratorProps} from 'akeneoassetmanager/application/configuration/value';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';

const View = ({id, value, locale, onChange, onSubmit, canEditData}: ViewGeneratorProps) => {
  const translate = useTranslate();
  const [reloadPreview, onReloadPreview] = useReloadPreview();
  const [isFullscreenModalOpen, openFullscreenModal, closeFullScreenModal] = useBooleanState();


  if (id === undefined) {
    id = `pim_asset_manager.asset.enrich.${value.attribute.code}`;
  }

  const mediaPreviewUrl = getMediaPreviewUrl({
    type: MediaPreviewType.ThumbnailSmall,
    attributeIdentifier: value.attribute.identifier,
    data: getMediaData(value.data),
  });

  useEffect(() => {
    if (reloadPreview) {
      doRegenerate();
    }
  }, [reloadPreview]);
  const [regenerate, doRegenerate, refreshedUrl] = useRegenerate(mediaPreviewUrl);
  const inModal = useInModal();

  const attributeLabel = getLabelInCollection(
    value.attribute.labels,
    localeReferenceStringValue(locale),
    true,
    value.attribute.code
  );

  if (!isMediaLinkData(value.data) || !isMediaLinkAttribute(value.attribute)) {
    return null;
  }

  const mediaLinkUrl = getMediaLinkUrl(value.data, value.attribute);

  return (
    <>
      <MediaLinkInput
        id={id}
        thumbnailUrl={regenerate ? null : refreshedUrl}
        value={mediaLinkDataStringValue(value.data)}
        onChange={newValue => onChange(setValueData(value, mediaLinkDataFromString(newValue)))}
        readOnly={!canEditData}
        placeholder={!canEditData ? '' : translate('pim_asset_manager.attribute.media_link.placeholder')}
        onSubmit={onSubmit}
      >
        <IconButton
          icon={<RefreshIcon />}
          title={translate('pim_asset_manager.attribute.media_link.reload')}
          onClick={onReloadPreview}
        />
        {canCopyToClipboard() && (
          <IconButton
            icon={<CopyIcon />}
            title={translate('pim_asset_manager.asset_preview.copy_url')}
            onClick={() => copyToClipboard(mediaLinkUrl)}
          />
        )}
        {canDownloadMediaLink(value.attribute) && (
          <IconButton
            href={mediaLinkUrl}
            target="_blank"
            download={mediaLinkUrl}
            icon={<DownloadIcon />}
            title={translate('pim_asset_manager.asset_preview.download')}
          />
        )}
        {!inModal && <IconButton
          onClick={openFullscreenModal}
          icon={<FullscreenIcon />}
          title={translate('pim_asset_manager.asset.button.fullscreen')}
        />}
      </MediaLinkInput>
      {isFullscreenModalOpen && !inModal && (
        <FullscreenPreview
          onClose={closeFullScreenModal}
          attribute={value.attribute}
          data={value.data}
          label={attributeLabel}
        />
      )}
    </>
  );
};

export const view = View;
