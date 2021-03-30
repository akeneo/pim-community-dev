import React from 'react';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {getMediaLinkUrl, isMediaLinkData} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {canCopyToClipboard, copyToClipboard, getImageDownloadUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {MediaData, isDataEmpty} from 'akeneoassetmanager/domain/model/asset/data';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {
  CopyIcon,
  DownloadIcon,
  RefreshIcon,
  IconButton,
  FullscreenIcon,
  ButtonProps,
  IconButtonProps,
  Button,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useReloadPreview} from 'akeneoassetmanager/application/hooks/useReloadPreview';

type ActionProps = {
  data: MediaData;
  attribute: NormalizedAttribute;
  label?: string;
};

const iconButtonProps: Partial<IconButtonProps> = {
  level: 'tertiary',
  ghost: 'borderless',
  size: 'small',
};

const buttonProps: Partial<ButtonProps> = {
  level: 'tertiary',
  ghost: true,
};

const DownloadAction = ({data, attribute, label}: ActionProps) => {
  const translate = useTranslate();

  if (
    (isMediaLinkAttribute(attribute) &&
      (MediaTypes.youtube === attribute.media_type || MediaTypes.vimeo === attribute.media_type)) ||
    isDataEmpty(data)
  )
    return null;

  const url = isMediaFileData(data) ? getImageDownloadUrl(data) : getMediaLinkUrl(data, attribute);
  const fileName = null !== data && isMediaFileData(data) ? data.originalFilename : url;
  const title = label || translate('pim_asset_manager.asset_preview.download');

  return undefined === label ? (
    <IconButton {...iconButtonProps} icon={<DownloadIcon />} title={title} />
  ) : (
    <Button {...buttonProps} href={url} download={fileName} target="_blank">
      <DownloadIcon />
      {label}
    </Button>
  );
};

const CopyUrlAction = ({data, attribute, label}: ActionProps) => {
  const translate = useTranslate();

  if (!isMediaLinkAttribute(attribute) || !isMediaLinkData(data) || isDataEmpty(data) || !canCopyToClipboard())
    return null;

  const url = getMediaLinkUrl(data, attribute);
  const title = label || translate('pim_asset_manager.asset_preview.copy_url');

  return undefined === label ? (
    <IconButton {...iconButtonProps} icon={<CopyIcon />} title={title} />
  ) : (
    <Button {...buttonProps} onClick={() => copyToClipboard(url)}>
      <CopyIcon />
      {label}
    </Button>
  );
};

const ReloadAction = ({data, attribute, label}: ActionProps) => {
  const translate = useTranslate();
  const [, onReloadPreview] = useReloadPreview();

  if (!isMediaLinkAttribute(attribute) || !isMediaLinkData(data)) return null;

  const title = label || translate('pim_asset_manager.attribute.media_link.reload');

  return <IconButton {...iconButtonProps} icon={<RefreshIcon />} title={title} onClick={onReloadPreview} />;
};

const FullscreenAction = ({onClick}: {onClick: () => void}) => {
  const translate = useTranslate();

  return (
    <IconButton
      {...iconButtonProps}
      icon={<FullscreenIcon />}
      onClick={onClick}
      title={translate('pim_asset_manager.asset.button.fullscreen')}
    />
  );
};

export {ReloadAction, FullscreenAction, DownloadAction, CopyUrlAction};
