import * as React from 'react';
import styled from 'styled-components';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoassetmanager/tools/translator';
import {RemoveButton} from 'akeneoassetmanager/application/component/app/remove-button';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import ListAsset, {
  getListAssetMainMediaThumbnail,
  getAssetLabel,
} from 'akeneoassetmanager/domain/model/asset/list-asset';

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
  asset: ListAsset;
  context: Context;
  onRemove: () => void;
  isLoading?: boolean;
}) => {
  const label = getAssetLabel(asset, context.locale);

  return (
    <Container
      data-loading={isLoading}
      data-code={asset.code}
      className={isLoading ? 'AknLoadingPlaceHolderContainer' : ''}
    >
      <AssetThumbnail
        src={getMediaPreviewUrl(getListAssetMainMediaThumbnail(asset, context.channel, context.locale))}
        width={44}
        height={44}
      />
      <AssetDetails>
        <AssetCode title={asset.code}>{asset.code}</AssetCode>
        <AssetLabel title={label}>{label}</AssetLabel>
      </AssetDetails>
      <RemoveButton
        title={__('pim_asset_manager.asset_picker.basket.remove_one_asset', {
          assetName: label,
        })}
        onClick={onRemove}
      />
    </Container>
  );
};

export default AssetItem;
