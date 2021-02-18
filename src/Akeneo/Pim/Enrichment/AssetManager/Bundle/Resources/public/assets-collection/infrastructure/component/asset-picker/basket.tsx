import React, {useState, useEffect} from 'react';
import styled from 'styled-components';
import {AssetsIllustration, getColor, getFontSize, Button} from 'akeneo-design-system';
import {Context} from 'akeneoassetmanager/domain/model/context';
import AssetItem from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/basket/asset-item';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import ListAsset, {createEmptyAsset, getAssetByCode} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type BasketProps = {
  dataProvider: any;
  selection: AssetCode[];
  assetFamilyIdentifier: AssetFamilyIdentifier;
  context: Context;
  onRemove: (assetCode: AssetCode) => void;
  onRemoveAll: () => void;
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
  display: flex;
  justify-content: center;
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
  const [assetCollection, setAssetCollection] = useState<ListAsset[]>([]);

  useEffect(() => {
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

const Basket = ({dataProvider, assetFamilyIdentifier, selection, context, onRemove, onRemoveAll}: BasketProps) => {
  const {assetCollection} = useLoadAssetCollection(selection, dataProvider, assetFamilyIdentifier, context);
  const translate = useTranslate();

  if (0 === selection.length) {
    return (
      <Container>
        <Title>{translate('pim_asset_manager.asset_picker.basket.empty_title')}</Title>
        <IllustrationContainer>
          <AssetsIllustration size={128} />
        </IllustrationContainer>
      </Container>
    );
  }

  return (
    <Container data-container="basket">
      <Title>{translate('pim_asset_manager.asset_selected', {assetCount: selection.length}, selection.length)}</Title>
      <List>
        {selection.map((assetCode: AssetCode) => {
          const asset = getAssetByCode(assetCollection, assetCode);
          if (undefined === asset) {
            return <AssetItem asset={createEmptyAsset(assetCode)} context={context} isLoading={true} key={assetCode} />;
          }
          return <AssetItem asset={asset} context={context} onRemove={() => onRemove(asset.code)} key={assetCode} />;
        })}
      </List>
      <Footer>
        <Button
          ghost={true}
          level="tertiary"
          title={translate('pim_asset_manager.asset_picker.basket.remove_all_assets')}
          tabIndex={1}
          onClick={onRemoveAll}
        >
          {translate('pim_asset_manager.asset_picker.basket.remove_all_assets')}
        </Button>
      </Footer>
    </Container>
  );
};

export default Basket;
