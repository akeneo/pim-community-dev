import * as React from 'react';
import {
  Asset,
  isAssetInCollection,
  addAssetToCollection,
  removeAssetFromCollection,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import AssetCard from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/asset-card';
import styled from 'styled-components';
import AssetIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/illustration/asset';
import __ from 'akeneoreferenceentity/tools/translator';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

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
  const Container = styled.div`
    display: flex;
    flex-wrap: wrap;
    margin: 0 40px;
  `;
  return (
    <Container>
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

const EmptyResult = () => {
  const Container = styled.div`
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
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

  return (
    <Container>
      <AssetIllustration size={256} />
      <Title>{__('pim_asset_manager.asset_picker.no_result.title')}</Title>
      <SubTitle>{__('pim_asset_manager.asset_picker.no_result.sub_title')}</SubTitle>
    </Container>
  );
};
