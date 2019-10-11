import * as React from 'react';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {
  Asset,
  emptyCollection,
  getAssetByCode,
  emptyAsset,
  removeAssetFromCollection,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import styled from 'styled-components';
import __ from 'akeneoreferenceentity/tools/translator';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import AssetIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/illustration/asset';
import AssetItem from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/basket/asset-item';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';

type BasketProps = {
  dataProvider: any;
  selection: AssetCode[];
  assetFamilyIdentifier: AssetFamilyIdentifier;
  context: Context;
  onSelectionChange: (assetCodeCollection: AssetCode[]) => void;
};

const Container = styled.div`
  width: 280px;
  border-left: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  padding: 0 20px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
`;

const Title = styled.div`
  padding-bottom: 10px;
  padding-top: 4px;
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.purple100};
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.small};
  text-transform: uppercase;
`;

const List = styled.ul`
  flex: 1;
  overflow-y: auto;
`;

const Footer = styled.div`
  border-top: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
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
  const [assetCollection, setAssetCollection] = React.useState([] as Asset[]);

  React.useEffect(() => {
    if (0 === selection.length) {
      setAssetCollection([]);
      return;
    }

    dataProvider.assetFetcher.fetchByCode(assetFamilyIdentifier, selection, context).then((receivedAssets: Asset[]) => {
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
                asset={emptyAsset(assetCode)}
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
              onRemove={() => onSelectionChange(removeAssetFromCollection(selection, asset.code))}
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
      <AssetIllustration size={128} />
    </IllustrationContainer>
  </Container>
);

export default Basket;
