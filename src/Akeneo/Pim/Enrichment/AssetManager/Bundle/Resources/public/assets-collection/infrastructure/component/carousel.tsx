import * as React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {Attribute, getAttributeLabel} from 'akeneoassetmanager/platform/model/structure/attribute';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Spacer} from 'akeneoassetmanager/application/component/app/spacer';
import {ResultCounter} from 'akeneoassetmanager/application/component/app/result-counter';
import ListAsset, {getListAssetMainMediaThumbnail} from 'akeneoassetmanager/domain/model/asset/list-asset';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';

const Container = styled.div``;

const Thumbnails = styled.div`
  display: flex;
  overflow-x: auto;
`;

const AssetThumbnail = styled.img<{highlighted: boolean}>`
  border: 2px solid
    ${(props: ThemedProps<{highlighted: boolean}>) =>
      props.highlighted ? props.theme.color.blue100 : props.theme.color.grey60};
  width: 80px;
  height: 80px;
  margin: 20px 20px 0 0;
  ${(props: ThemedProps<{highlighted: boolean}>) => !props.highlighted && `opacity: 0.6`};
  object-fit: contain;
`;

const Header = styled.div`
  display: flex;
  align-items: baseline;
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey140};
  padding: 13px 0;
`;

const Title = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  text-transform: uppercase;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.big};
`;

type CarouselProps = {
  assetCollection: ListAsset[];
  selectedAssetCode: AssetCode;
  productAttribute: Attribute;
  context: Context;
  onAssetChange: (assetCode: AssetCode) => void;
};

export const Carousel = ({
  assetCollection,
  selectedAssetCode,
  productAttribute,
  context,
  onAssetChange,
}: CarouselProps) => (
  <Container>
    <Header>
      <Title>{getAttributeLabel(productAttribute, context.locale)}</Title>
      <ResultCounter count={assetCollection.length} labelKey={'pim_asset_manager.asset_counter'} />
      <Spacer />
    </Header>
    <Thumbnails>
      {assetCollection.map(asset => (
        <AssetThumbnail
          data-role={`carousel-thumbnail-${asset.code}`}
          key={asset.code}
          highlighted={selectedAssetCode === asset.code}
          src={getMediaPreviewUrl(getListAssetMainMediaThumbnail(asset, context.channel, context.locale))}
          onClick={() => onAssetChange(asset.code)}
        />
      ))}
    </Thumbnails>
  </Container>
);
