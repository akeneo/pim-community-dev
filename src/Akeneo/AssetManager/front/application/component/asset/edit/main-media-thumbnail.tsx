import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import EditionAsset, {
  getEditionAssetLabel,
  getEditionAssetMainMediaPreview,
} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';

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

export const MainMediaThumbnail = ({asset, context}: MainMediaThumbnailProps) => (
  <Container>
    <Img
      alt={__('pim_asset_manager.asset.img', {label: getEditionAssetLabel(asset, context.locale)})}
      src={getMediaPreviewUrl(getEditionAssetMainMediaPreview(asset, context.channel, context.locale))}
    />
  </Container>
);
