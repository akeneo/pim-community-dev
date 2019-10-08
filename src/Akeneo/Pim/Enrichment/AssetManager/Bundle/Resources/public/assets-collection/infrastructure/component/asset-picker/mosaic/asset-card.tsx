import * as React from 'react';
import {Asset, getAssetLabel, getImage} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import styled from 'styled-components';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import Checkbox from 'akeneopimenrichmentassetmanager/platform/component/common/checkbox';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import CompletenessBadge from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/mosaic/completeness-badge';

const Container = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
  height: 165px;
  margin: 10px 20px 10px 0;
  justify-content: space-between;
`;
const Title = styled.div`
  display: flex;
  width: 140px;
  align-items: center;
  min-height: 15px;
`;
type ThumbnailProps = {isSelected: boolean};
const Thumbnail = styled.img<ThumbnailProps>`
  border-width: ${(props: ThemedProps<ThumbnailProps>) => (props.isSelected ? '2px' : '1px')};
  border-color: ${(props: ThemedProps<ThumbnailProps>) =>
    props.isSelected ? props.theme.color.blue100 : props.theme.color.grey100};
  border-style: solid;
  width: 140px;
  height: 140px;
`;

const AssetCompleteness = styled.div`
  position: absolute;
  top: 10px;
  right: 10px;
`;

const AssetCard = ({
  asset,
  context,
  isSelected,
  onSelectionChange,
}: {
  asset: Asset;
  context: Context;
  isSelected: boolean;
  onSelectionChange: (code: AssetCode, value: boolean) => void;
}) => {
  return (
    <Container data-asset={asset.code} data-selected={isSelected}>
      <AssetCompleteness>
        <CompletenessBadge completeness={asset.completeness} />
      </AssetCompleteness>
      <Thumbnail
        src={getImage(asset)}
        isSelected={isSelected}
        onClick={() => onSelectionChange(asset.code, !isSelected)}
      />
      <Title>
        <Checkbox
          value={isSelected}
          onChange={(value: boolean) => {
            onSelectionChange(asset.code, value);
          }}
        />
        <Label color={isSelected ? akeneoTheme.color.blue100 : undefined}>{getAssetLabel(asset, context.locale)}</Label>
      </Title>
    </Container>
  );
};

export default AssetCard;
