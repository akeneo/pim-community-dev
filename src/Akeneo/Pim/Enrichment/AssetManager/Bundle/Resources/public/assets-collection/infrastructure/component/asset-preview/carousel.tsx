import * as React from 'react';
import styled from 'styled-components';
import {Asset} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetCounter from 'akeneopimenrichmentassetmanager/platform/component/common/asset-counter';
import __ from 'akeneoassetmanager/tools/translator';
import {Attribute, getAttributeLabel} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Spacer} from 'akeneopimenrichmentassetmanager/platform/component/common';
import {getAssetPreview, MediaPreviewTypes} from 'akeneoassetmanager/tools/media-url-generator';

const AssetThumbnail = styled.img<{highlighted: boolean}>`
  border: 2px solid
    ${(props: ThemedProps<{highlighted: boolean}>) =>
      props.highlighted ? props.theme.color.blue100 : props.theme.color.grey60};
  width: 80px;
  height: 80px;
  margin: 20px 20px 0 0;
  ${(props: ThemedProps<{highlighted: boolean}>) => !props.highlighted && `opacity: 0.6`};
  object-fit: cover;

  &:hover {
    object-fit: contain;
  }
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
  assetCollection: Asset[];
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
}: CarouselProps) => {
  return (
    <React.Fragment>
      <Header>
        <Title>{getAttributeLabel(productAttribute, context.locale)}</Title>
        <AssetCounter resultCount={assetCollection.length} />
        <Spacer />
      </Header>
      {assetCollection.map(asset => (
        <AssetThumbnail
          data-role={`carousel-thumbnail-${asset.code}`}
          key={asset.code}
          highlighted={selectedAssetCode === asset.code}
          src={getAssetPreview(asset, MediaPreviewTypes.Thumbnail)}
          onClick={() => onAssetChange(asset.code)}
        />
      ))}
    </React.Fragment>
  );
};
