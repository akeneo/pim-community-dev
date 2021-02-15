import React from 'react';
import styled from 'styled-components';
import {CloseIcon, getColor, getFontSize, IconButton} from 'akeneo-design-system';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import ListAsset, {
  getListAssetMainMediaThumbnail,
  getAssetLabel,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.li`
  padding: 10px 0;
  display: flex;
  justify-content: space-between;
  align-items: center;

  :not(:last-child) {
    border-bottom: 1px solid ${getColor('grey', 80)};
  }
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
  font-size: ${getFontSize('small')};
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

const AssetLabel = styled.div`
  line-height: 16px;
  color: ${getColor('brand', 100)};
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
  onRemove?: () => void;
  isLoading?: boolean;
}) => {
  const label = getAssetLabel(asset, context.locale);
  const previewUrl = getMediaPreviewUrl(getListAssetMainMediaThumbnail(asset, context.channel, context.locale));
  const [, , refreshedUrl] = useRegenerate(previewUrl);
  const translate = useTranslate();

  return (
    <Container
      data-loading={isLoading}
      data-code={asset.code}
      className={isLoading ? 'AknLoadingPlaceHolderContainer' : ''}
    >
      <AssetThumbnail src={refreshedUrl} width={44} height={44} />
      <AssetDetails>
        <AssetCode title={asset.code}>{asset.code}</AssetCode>
        <AssetLabel title={label}>{label}</AssetLabel>
      </AssetDetails>
      <IconButton
        icon={<CloseIcon />}
        level="tertiary"
        ghost="borderless"
        title={translate('pim_asset_manager.asset_picker.basket.remove_one_asset', {
          assetName: label,
        })}
        onClick={onRemove}
      />
    </Container>
  );
};

export default AssetItem;
