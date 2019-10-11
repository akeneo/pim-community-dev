import * as React from 'react';
import {
  Asset,
  isAssetInCollection,
  addAssetToCollection,
  removeAssetFromCollection,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import AssetCard from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/mosaic/asset-card';
import styled from 'styled-components';
import AssetIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/illustration/asset';
import __ from 'akeneoreferenceentity/tools/translator';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  flex: 1;
  display: grid;
  grid-gap: 20px;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  margin-top: 20px;
`;

const Mosaic = ({
  context,
  selection,
  onSelectionChange,
  assetCollection,
}: {
  selection: AssetCode[];
  assetCollection: Asset[];
  context: Context;
  onSelectionChange: (selection: AssetCode[]) => void;
}) => {
  return (
    <Container data-container="mosaic">
      {assetCollection.length > 0 ? (
        assetCollection.map((asset: Asset) => {
          return (
            <AssetCard
              key={asset.code}
              asset={asset}
              context={context}
              isSelected={isAssetInCollection(asset.code, selection)}
              onSelectionChange={(code: AssetCode, isChecked: boolean) => {
                const newSelection = isChecked
                  ? addAssetToCollection(selection, code)
                  : removeAssetFromCollection(selection, code);
                onSelectionChange(newSelection);
              }}
            />
          );
        })
      ) : (
        <EmptyResult />
      )}
    </Container>
  );
};

export default Mosaic;

const EmptyContainer = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  justify-content: center;
`;
const Title = styled.div`
  margin-bottom: 10px;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
`;
const SubTitle = styled.div`
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.bigger};
  color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
`;

const EmptyResult = () => {
  return (
    <EmptyContainer>
      <AssetIllustration size={256} />
      <Title>{__('pim_asset_manager.asset_picker.no_result.title')}</Title>
      <SubTitle>{__('pim_asset_manager.asset_picker.no_result.sub_title')}</SubTitle>
    </EmptyContainer>
  );
};
