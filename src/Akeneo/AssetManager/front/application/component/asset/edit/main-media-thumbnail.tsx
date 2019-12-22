import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import EditionAsset, {getEditionAssetLabel} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {getValue} from 'akeneoassetmanager/domain/model/asset/value-collection';
import {MediaPreviewType, getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {getEditionValueMediaPreview} from 'akeneoassetmanager/domain/model/asset/edition-value';

type MainMediaThumbnailProps = {
  asset: EditionAsset;
  context: {
    channel: ChannelCode;
    locale: LocaleCode;
  };
};

const Container = styled.div`
  position: relative;
  width: 142px;
  height: 142px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  margin-right: 20px;
  border-radius: 4px;
  display: flex;
  overflow: hidden;
  flex-basis: 142px;
  flex-shrink: 0;
`;

const Img = styled.img`
  margin: auto;
  width: 100%;
  max-height: 140px;
  transition: filter 0.3s;
  z-index: 0;
  object-fit: contain;
`;

export const getEditionAssetMainMediaUrl = (asset: EditionAsset, channel: ChannelCode, locale: LocaleCode): string => {
  const attributeAsMainMediaIdentifier = asset.assetFamily.attributeAsMainMedia;
  const mediaValue = getValue(asset.values, attributeAsMainMediaIdentifier, channel, locale);
  if (undefined === mediaValue) return '';

  return getMediaPreviewUrl(
    getEditionValueMediaPreview(MediaPreviewType.Thumbnail, mediaValue, attributeAsMainMediaIdentifier)
  );
};

export const MainMediaThumbnail = ({asset, context}: MainMediaThumbnailProps) => (
  <Container>
    <Img
      alt={__('pim_asset_manager.asset.img', {label: getEditionAssetLabel(asset, context.locale)})}
      src={getEditionAssetMainMediaUrl(asset, context.channel, context.locale)}
    />
  </Container>
);
