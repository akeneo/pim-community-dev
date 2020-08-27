import * as React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {Attribute, getAttributeLabel} from 'akeneoassetmanager/platform/model/structure/attribute';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {ResultCounter} from 'akeneoassetmanager/application/component/app/result-counter';
import ListAsset, {getListAssetMainMediaThumbnail} from 'akeneoassetmanager/domain/model/asset/list-asset';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {useKeepVisibleX} from 'akeneoassetmanager/application/hooks/scroll';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';

const Container = styled.div``;

const Thumbnails = styled.div`
  display: flex;
  overflow-x: auto;
  padding: 10px 0;
`;

const AssetThumbnail = styled.img<{highlighted: boolean}>`
  border: 2px solid
    ${(props: ThemedProps<{highlighted: boolean}>) =>
      props.highlighted ? props.theme.color.blue100 : props.theme.color.grey60};
  width: 80px;
  height: 80px;
  ${(props: ThemedProps<{highlighted: boolean}>) => !props.highlighted && `opacity: 0.6`};
  object-fit: contain;
  flex-shrink: 0;

  :not(:last-child) {
    margin-right: 20px;
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
}: CarouselProps) => {
  const {containerRef, elementRef} = useKeepVisibleX<HTMLDivElement>();

  return (
    <Container>
      <Header>
        <Title>{getAttributeLabel(productAttribute, context.locale)}</Title>
        <ResultCounter count={assetCollection.length} labelKey={'pim_asset_manager.asset_counter'} />
      </Header>
      <Thumbnails ref={containerRef}>
        {assetCollection.map(asset => {
          const previewUrl = getMediaPreviewUrl(getListAssetMainMediaThumbnail(asset, context.channel, context.locale));
          const [, , refreshedUrl] = useRegenerate(previewUrl);

          return (
            <AssetThumbnail
              data-role={`carousel-thumbnail-${asset.code}`}
              ref={selectedAssetCode === asset.code ? elementRef : undefined}
              key={asset.code}
              highlighted={selectedAssetCode === asset.code}
              src={refreshedUrl}
              onClick={() => onAssetChange(asset.code)}
            />
          );
        })}
      </Thumbnails>
    </Container>
  );
};
