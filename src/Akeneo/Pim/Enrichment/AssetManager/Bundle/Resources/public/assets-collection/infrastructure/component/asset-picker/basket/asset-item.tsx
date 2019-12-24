import * as React from 'react';
import styled from 'styled-components';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {Asset, getAssetLabel} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoassetmanager/tools/translator';
import {RemoveButton} from 'akeneoassetmanager/application/component/app/remove-button';
import {getAssetPreviewLegacy} from 'akeneoassetmanager/tools/media-url-generator';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';

const Container = styled.li`
  padding: 10px 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
`;

const AssetThumbnail = styled.img`
  width: 44px;
  height: 44px;
  flex-shrink: 0;
  object-fit: contain;
`;

const AssetDetails = styled.div`
  flex-grow: 1;
  padding: 0 10px;
  overflow: hidden;
`;

const AssetCode = styled.div`
  margin-bottom: 2px;
  line-height: 13px;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.small};
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

const AssetLabel = styled.div`
  line-height: 16px;
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  font-weight: bolditalic;
  font-style: italic;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

const AssetItem = ({
  asset,
  context,
  onRemove,
  isLoading = false,
}: {
  asset: Asset;
  context: Context;
  onRemove: () => void;
  isLoading?: boolean;
}) => {
  return (
    <Container
      data-loading={isLoading}
      data-code={asset.code}
      className={isLoading ? 'AknLoadingPlaceHolderContainer' : ''}
    >
      <AssetThumbnail src={getAssetPreviewLegacy(asset, MediaPreviewType.Thumbnail, context)} width={44} height={44} />
      <AssetDetails>
        <AssetCode title={asset.code}>{asset.code}</AssetCode>
        <AssetLabel title={getAssetLabel(asset, context.locale)}>{getAssetLabel(asset, context.locale)}</AssetLabel>
      </AssetDetails>
      <RemoveButton
        title={__('pim_asset_manager.asset_picker.basket.remove_one_asset', {
          assetName: getAssetLabel(asset, context.locale),
        })}
        onClick={onRemove}
      />
    </Container>
  );
};

export default AssetItem;
