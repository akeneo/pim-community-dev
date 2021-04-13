import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {Attribute, getAttributeLabel} from 'akeneoassetmanager/platform/model/structure/attribute';
import {Context} from 'akeneoassetmanager/domain/model/context';
import ListAsset, {
  getAssetLabel,
  getListAssetMainMediaThumbnail,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {useKeepVisibleX} from 'akeneoassetmanager/application/hooks/scroll';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';

const Container = styled.div``;

const Thumbnails = styled.div`
  display: flex;
  overflow-x: auto;
  padding: 10px 0;
`;

const AssetThumbnail = styled.img<{highlighted: boolean} & AkeneoThemedProps>`
  border: 2px solid ${({highlighted}) => (highlighted ? getColor('blue', 100) : getColor('grey', 60))};
  width: 80px;
  height: 80px;
  opacity: ${({highlighted}) => (highlighted ? 1 : 0.6)};
  object-fit: contain;
  flex-shrink: 0;

  :not(:last-child) {
    margin-right: 20px;
  }
`;

type CarouselProps = {
  assetCollection: ListAsset[];
  selectedAssetCode: AssetCode;
  productAttribute: Attribute;
  context: Context;
  onAssetChange: (assetCode: AssetCode) => void;
};

const Carousel = ({assetCollection, selectedAssetCode, productAttribute, context, onAssetChange}: CarouselProps) => {
  const {containerRef, elementRef} = useKeepVisibleX<HTMLDivElement>();
  const translate = useTranslate();

  return (
    <Container>
      <SectionTitle>
        <SectionTitle.Title>{getAttributeLabel(productAttribute, context.locale)}</SectionTitle.Title>
        <SectionTitle.Information>
          {translate('pim_asset_manager.asset_counter', {count: assetCollection.length}, assetCollection.length)}
        </SectionTitle.Information>
      </SectionTitle>
      <Thumbnails ref={containerRef} role="listbox">
        {assetCollection.map(asset => {
          const previewUrl = getMediaPreviewUrl(getListAssetMainMediaThumbnail(asset, context.channel, context.locale));
          const [, , refreshedUrl] = useRegenerate(previewUrl);
          const assetLabel = getAssetLabel(asset, context.locale);

          return (
            <AssetThumbnail
              alt={assetLabel}
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

export {Carousel};
