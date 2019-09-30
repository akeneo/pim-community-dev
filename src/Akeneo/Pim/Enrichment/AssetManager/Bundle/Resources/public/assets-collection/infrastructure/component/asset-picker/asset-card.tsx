import * as React from 'react';
import {Asset, getAssetLabel, getImage} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import styled from 'styled-components';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import Checkbox from 'akeneopimenrichmentassetmanager/platform/component/common/checkbox';

const Container = styled.div`
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
const Thumbnail = styled.img`
  width: 140px;
  height: 140px;
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
      <Thumbnail src={getImage(asset)} />
      <Title>
        <Checkbox
          value={isSelected}
          onChange={(value: boolean) => {
            onSelectionChange(asset.code, value);
          }}
        />
        <Label> {getAssetLabel(asset, context.locale)} </Label>
      </Title>
    </Container>
  );
};

export default AssetCard;
