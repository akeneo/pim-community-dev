import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {
  getMediaLinkUrl,
  isMediaLinkData,
  getYouTubeWatchUrl,
  getVimeoWatchUrl,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import Download from 'akeneoassetmanager/application/component/app/icon/download';
import {canCopyToClipboard, copyToClipboard, getImageDownloadUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {MediaData, isDataEmpty} from 'akeneoassetmanager/domain/model/asset/data';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {Copy} from 'akeneoassetmanager/application/component/app/icon/copy';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import ReloadIcon from 'akeneoassetmanager/application/component/app/icon/reload';

export const Container = styled.div`
  align-items: center;
  border-radius: 2px;
  border: 1px solid ${props => props.theme.color.grey80};
  display: flex;
  flex: 1;
  justify-content: center;
  max-width: 460px;
  padding: 15px;
`;

export const Thumbnail = styled.img`
  border: 1px solid ${props => props.theme.color.grey70};
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  margin-right: 15px;
  object-fit: cover;
`;

export const ThumbnailPlaceholder = styled.div`
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  margin-right: 15px;
`;

export const Action = styled.a`
  display: flex;
  align-items: center;

  &:hover {
    cursor: pointer;
  }
`;

export const Actions = styled.div<{margin?: number}>`
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: ${props => props.theme.fontSize.default};
  line-height: initial;

  > ${Action} {
    margin-left: ${props => props.margin}px;
  }
`;

Actions.defaultProps = {
  margin: 15,
};

export const ActionLabel = styled.span`
  margin-left: 5px;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey100};
`;

type ActionProps = {
  data: MediaData;
  attribute: NormalizedAttribute;
  label?: string;
  size?: number;
  color?: string;
};

export const DownloadAction = ({data, attribute, label, size, color}: ActionProps) => {
  if (
    (isMediaLinkAttribute(attribute) &&
      (MediaTypes.youtube === attribute.media_type || MediaTypes.vimeo === attribute.media_type)) ||
    isDataEmpty(data)
  )
    return null;

  const url = isMediaFileData(data) ? getImageDownloadUrl(data) : getMediaLinkUrl(data, attribute);
  const fileName = null !== data && isMediaFileData(data) ? data.originalFilename : url;
  const title = label || __('pim_asset_manager.asset_preview.download');

  return (
    <Action title={title} href={url} download={fileName} target="_blank">
      <Download size={size} color={color} title={title} />
      {label && <ActionLabel>{label}</ActionLabel>}
    </Action>
  );
};

export const CopyUrlAction = ({data, attribute, label, size, color}: ActionProps) => {
  if (!isMediaLinkAttribute(attribute) || !isMediaLinkData(data) || isDataEmpty(data) || !canCopyToClipboard())
    return null;

  const url =
    MediaTypes.youtube === attribute.media_type
      ? getYouTubeWatchUrl(data)
      : MediaTypes.vimeo === attribute.media_type
      ? getVimeoWatchUrl(data)
      : getMediaLinkUrl(data, attribute);
  const title = label || __('pim_asset_manager.asset_preview.copy_url');

  return (
    <Action title={title} onClick={() => copyToClipboard(url)}>
      <Copy size={size} color={color} title={title} />
      {label && <ActionLabel>{label}</ActionLabel>}
    </Action>
  );
};

export const ReloadAction = ({data, onReload, attribute, label, size, color}: ActionProps & {onReload: () => void}) => {
  if (!isMediaLinkAttribute(attribute) || !isMediaLinkData(data)) return null;
  const title = label || __('pim_asset_manager.attribute.media_link.reload');

  return (
    <Action title={title} onClick={onReload}>
      <ReloadIcon size={size} color={color} title={title} />
      {label && <ActionLabel>{label}</ActionLabel>}
    </Action>
  );
};
