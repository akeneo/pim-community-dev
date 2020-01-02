import * as React from 'react';
import {Context} from 'akeneoassetmanager/domain/model/context';
import styled from 'styled-components';
import {Label} from 'akeneoassetmanager/application/component/app/label';
import Checkbox from 'akeneoassetmanager/application/component/app/checkbox';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import CompletenessBadge from 'akeneoassetmanager/application/component/asset/list/mosaic/completeness-badge';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import ListAsset, {
  assetHasCompleteness,
  getAssetLabel,
  getListAssetMainMediaThumbnail,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

type ContainerProps = {isDisabled: boolean};
const Container = styled.div<ContainerProps>`
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  justify-content: space-between;
  cursor: ${(props: ContainerProps) => (props.isDisabled ? 'not-allowed' : 'auto')};
`;
const Title = styled.div`
  display: flex;
  align-items: center;
  min-height: 15px;
`;
type ThumbnailProps = {isSelected: boolean};
const Thumbnail = styled.img<ThumbnailProps>`
  object-fit: contain;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
`;

const AssetCompleteness = styled.div`
  position: absolute;
  top: 10px;
  right: 10px;
`;

const ImageContainer = styled.div`
  width: 100%;
  padding-top: 100%; /* 1:1 Aspect Ratio */
  position: relative;
  border-width: ${(props: ThemedProps<ThumbnailProps>) => (props.isSelected ? '2px' : '1px')};
  width: ${(props: ThemedProps<ThumbnailProps>) => (props.isSelected ? 'calc(100% - 2px)' : '100%')};
  border-color: ${(props: ThemedProps<ThumbnailProps>) =>
    props.isSelected ? props.theme.color.blue100 : props.theme.color.grey100};
  border-style: solid;
  margin-bottom: 6px;
  min-height: 140px;
`;

const AssetCard = ({
  asset,
  context,
  isSelected,
  onSelectionChange,
  isDisabled,
  onClick,
}: {
  asset: ListAsset;
  context: Context;
  isSelected: boolean;
  isDisabled: boolean;
  onSelectionChange: (code: AssetCode, value: boolean) => void;
  onClick?: (code: AssetCode) => void;
}) => {
  return (
    <Container data-asset={asset.code} data-selected={isSelected} isDisabled={isDisabled}>
      <ImageContainer>
        <Thumbnail
          src={getMediaPreviewUrl(getListAssetMainMediaThumbnail(asset, context.channel, context.locale))}
          isSelected={isSelected}
          onClick={() => {
            if (onClick) {
              onClick(asset.code);
            } else if (!isDisabled) {
              onSelectionChange(asset.code, !isSelected);
            }
          }}
        />
      </ImageContainer>
      {assetHasCompleteness(asset) && (
        <AssetCompleteness>
          <CompletenessBadge completeness={asset.completeness} />
        </AssetCompleteness>
      )}
      <Title>
        {!onClick && (
          <Checkbox
            value={isSelected}
            onChange={(value: boolean) => onSelectionChange(asset.code, value)}
            readOnly={isDisabled}
          />
        )}
        <Label color={isSelected ? akeneoTheme.color.blue100 : undefined}>{getAssetLabel(asset, context.locale)}</Label>
      </Title>
    </Container>
  );
};

export default AssetCard;
