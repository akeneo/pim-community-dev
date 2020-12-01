import React from 'react';
import {Context} from 'akeneoassetmanager/domain/model/context';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import AssetItem from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/basket/asset-item';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import ListAsset, {
  createEmptyAsset,
  getAssetByCode,
  removeAssetFromAssetCodeCollection,
  emptyCollection,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetsIllustration, getColor, getFontSize} from 'akeneo-design-system';

type BasketProps = {
  dataProvider: any;
  selection: AssetCode[];
  assetFamilyIdentifier: AssetFamilyIdentifier;
  context: Context;
  onSelectionChange: (assetCodeCollection: AssetCode[]) => void;
};

const Container = styled.div`
  width: 300px;
  border-left: 1px solid ${getColor('grey', 80)};
  padding: 0 20px 20px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
`;

const Title = styled.div`
  line-height: 43px;
  border-bottom: 1px solid ${getColor('brand', 100)};
  color: ${getColor('brand', 120)};
  font-size: ${getFontSize('small')};
  text-transform: uppercase;
`;

const List = styled.ul`
  flex: 1;
  overflow-y: auto;
`;

const Footer = styled.div`
  border-top: 1px solid ${getColor('grey', 80)};
  padding-top: 10px;
`;

const RemoveAllButton = styled(Button)`
  max-width: max-content;
  margin: 0 auto;
`;

const IllustrationContainer = styled.div`
  margin-top: 20px;
  text-align: center;
`;

const useLoadAssetCollection = (
  selection: AssetCode[],
  dataProvider: any,
  assetFamilyIdentifier: AssetFamilyIdentifier,
  context: Context
) => {
  const [assetCollection, setAssetCollection] = React.useState<ListAsset[]>([]);

  React.useEffect(() => {
    if (0 === selection.length) {
      setAssetCollection([]);
      return;
    }

    dataProvider.assetFetcher
      .fetchByCode(assetFamilyIdentifier, selection, context)
      .then((receivedAssets: ListAsset[]) => {
        setAssetCollection(receivedAssets);
      });
  }, [selection]);

  return {assetCollection, setAssetCollection};
};

const Basket = ({dataProvider, assetFamilyIdentifier, selection, context, onSelectionChange}: BasketProps) => {
  const {assetCollection} = useLoadAssetCollection(selection, dataProvider, assetFamilyIdentifier, context);

  if (0 === selection.length) {
    return <EmptyResult />;
  }

  return (
    <Container data-container="basket">
      <Title>
        {__('pim_asset_manager.asset_picker.basket.title', {assetCount: selection.length}, selection.length)}
      </Title>
      <List>
        {selection.map((assetCode: AssetCode) => {
          const asset = getAssetByCode(assetCollection, assetCode);
          if (undefined === asset) {
            return (
              <AssetItem
                asset={createEmptyAsset(assetCode)}
                context={context}
                onRemove={() => {}}
                isLoading={true}
                key={assetCode}
              />
            );
          }
          return (
            <AssetItem
              asset={asset}
              context={context}
              onRemove={() => onSelectionChange(removeAssetFromAssetCodeCollection(selection, asset.code))}
              key={assetCode}
            />
          );
        })}
      </List>
      <Footer>
        <RemoveAllButton
          buttonSize="default"
          color="outline"
          title={__('pim_asset_manager.asset_picker.basket.remove_all_assets')}
          tabIndex={1}
          onClick={() => onSelectionChange(emptyCollection(selection))}
        >
          {__('pim_asset_manager.asset_picker.basket.remove_all_assets')}
        </RemoveAllButton>
      </Footer>
    </Container>
  );
};

const EmptyResult = () => (
  <Container>
    <Title>{__('pim_asset_manager.asset_picker.basket.empty_title')}</Title>
    <IllustrationContainer>
      <AssetsIllustration size={128} />
    </IllustrationContainer>
  </Container>
);

export default Basket;
