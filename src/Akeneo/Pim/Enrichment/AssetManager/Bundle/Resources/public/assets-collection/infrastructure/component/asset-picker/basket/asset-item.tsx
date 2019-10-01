import * as React from 'react';
import styled from 'styled-components';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {Asset, getAssetLabel} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoreferenceentity/tools/translator';
import {RemoveButton} from 'akeneoassetmanager/application/component/app/remove-button';

const Container = styled.li`
  padding: 10px 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

const AssetThumbnail = styled.img`
  width: 44px;
  height: 44px;
`;

const AssetDetails = styled.div`
  flex-grow: 1;
  padding: 0 10px;
`;

const AssetCode = styled.div`
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.small};
  text-overflow: ellipsis;
`;

const AssetLabel = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  font-weight: bolditalic;
  text-overflow: ellipsis;
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
      <AssetThumbnail src={asset.image} />
      <AssetDetails>
        <AssetCode>{asset.code}</AssetCode>
        <AssetLabel>{getAssetLabel(asset, context.locale)}</AssetLabel>
      </AssetDetails>
      <RemoveButton
        title={__('pim_asset_manager.asset_picker.basket.remove_one_asset', {
          assetName: getAssetLabel(asset, context.locale),
        })}
        onAction={onRemove}
      />
    </Container>
  );
};

export default AssetItem;
