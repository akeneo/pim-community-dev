import React from 'react';
import styled from 'styled-components';
import {ProductIdentifier} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {Pill} from 'akeneoassetmanager/application/component/app/pill';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Label} from 'akeneoassetmanager/application/component/app/label';
import __ from 'akeneoassetmanager/tools/translator';
import {ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {Thumbnail} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection/thumbnail';
import {AssetPreview} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-preview';
import {Attribute} from 'akeneoassetmanager/platform/model/structure/attribute';
import Key from 'akeneoassetmanager/tools/key';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import ListAsset, {
  getAssetCodes,
  sortAssetCollection,
  canAddAssetToCollection,
  removeAssetFromAssetCollection,
  MoveDirection,
  moveAssetInCollection,
  getAssetLabel,
  isComplete,
  ASSET_COLLECTION_LIMIT,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetFamilyDataProvider} from 'akeneoassetmanager/application/hooks/asset-family';
import {useShortcut} from 'akeneoassetmanager/application/hooks/input';
import {AssetsIllustration, Helper} from 'akeneo-design-system';

const AssetCard = styled.div`
  display: flex;
  flex-direction: column;
  height: 165px;
  margin-top: 10px;
  justify-content: space-between;
  margin-right: 20px;
`;

const AssetTitle = styled.div`
  display: flex;
  width: 140px;
  align-items: baseline;
  min-height: 15px;
`;

const BaselinePill = styled(Pill)`
  align-self: unset;
`;

const EmptyAssetCollection = styled.div<{readonly: boolean}>`
  height: 140px;
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  padding: 20px;
  border: 1px solid ${(props: ThemedProps<{readonly: boolean}>) => props.theme.color.grey80};
  opacity: ${(props: ThemedProps<{readonly: boolean}>) => (props.readonly ? 0.4 : 1)};
  margin: 10px 0;
`;

const Container = styled.div`
  display: flex;
  flex-wrap: wrap;
`;

type AssetCollectionProps = {
  productIdentifier: ProductIdentifier | null;
  productAttribute: Attribute;
  assetCodes: AssetCode[];
  readonly: boolean;
  context: ContextState;
  onChange: (value: AssetCode[]) => void;
};

const useLoadAssets = (
  assetCodes: AssetCode[],
  assetFamilyIdentifier: AssetFamilyIdentifier,
  context: ContextState
) => {
  const [assets, setAssets] = React.useState<ListAsset[]>([]);
  const hasChangeInCollection = (assetCodes: AssetCode[], assets: ListAsset[]) => {
    const collectionSizesAreTheSame = assets.length === assetCodes.length;
    const fetchedAssetCollectionIsEmpty = assets.length === 0;
    const arrayCodesAreIdentical = getAssetCodes(assets)
      .sort()
      .every((assetCode: AssetCode, index: number) => [...assetCodes].sort().indexOf(assetCode) === index);

    return !(collectionSizesAreTheSame && (fetchedAssetCollectionIsEmpty || arrayCodesAreIdentical));
  };

  React.useEffect(() => {
    if (assetCodes.length === 0) {
      setAssets([]);
      return;
    }
    if (hasChangeInCollection(assetCodes, assets)) {
      assetFetcher.fetchByCodes(assetFamilyIdentifier, assetCodes, context).then((receivedAssets: ListAsset[]) => {
        setAssets(sortAssetCollection(receivedAssets, assetCodes));
      });
    }
  }, [assetCodes, assetFamilyIdentifier, context]);

  return {assets, setAssets};
};

const assetPreviewDataProvider: AssetFamilyDataProvider = {
  assetFamilyFetcher,
};

export const AssetCollection = ({
  productIdentifier,
  productAttribute,
  assetCodes,
  readonly,
  context,
  onChange,
}: AssetCollectionProps) => {
  const assetFamilyIdentifier = productAttribute.referenceDataName;
  const [isPreviewModalOpen, setPreviewModalOpen] = React.useState<boolean>(false);
  const [initialPreviewAssetCode, setInitialPreviewAssetCode] = React.useState<AssetCode | null>(null);
  const {assets, setAssets} = useLoadAssets(assetCodes, assetFamilyIdentifier, context);

  useShortcut(Key.Escape, () => setPreviewModalOpen(false));

  return (
    <Container>
      {/* Collection is not empty and is loaded (we also need to check assetCodes because in this case we don't update the fetched assets */}
      {0 !== assetCodes.length ? (
        <React.Fragment>
          {!canAddAssetToCollection(assetCodes) && (
            <Helper>
              {__('pim_asset_manager.asset_collection.notification.limit', {limit: ASSET_COLLECTION_LIMIT})}
            </Helper>
          )}
          {assets.map((asset: ListAsset) => (
            <AssetCard key={asset.code} data-asset={asset.code}>
              <Thumbnail
                asset={asset}
                context={context}
                readonly={readonly}
                assetCollection={assets}
                onRemove={() => {
                  const filteredAssets = removeAssetFromAssetCollection(assets, asset.code);
                  setAssets(filteredAssets);
                  onChange(getAssetCodes(filteredAssets));
                }}
                onMove={(direction: MoveDirection) => {
                  const orderedAssets = moveAssetInCollection(assets, asset, direction);
                  setAssets(orderedAssets);
                  onChange(getAssetCodes(orderedAssets));
                }}
                onClick={() => {
                  setInitialPreviewAssetCode(asset.code);
                  setPreviewModalOpen(true);
                }}
              />
              <AssetTitle>
                <Label color={readonly ? akeneoTheme.color.grey100 : undefined}>
                  {getAssetLabel(asset, context.locale)}
                </Label>
                {!isComplete(asset) ? <BaselinePill /> : null}
              </AssetTitle>
            </AssetCard>
          ))}
          {isPreviewModalOpen && null !== initialPreviewAssetCode ? (
            <AssetPreview
              productIdentifier={productIdentifier}
              productAttribute={productAttribute}
              assetCollection={assets}
              initialAssetCode={initialPreviewAssetCode}
              context={context}
              onClose={() => setPreviewModalOpen(false)}
              assetFamilyIdentifier={assetFamilyIdentifier}
              dataProvider={assetPreviewDataProvider}
            />
          ) : null}
        </React.Fragment>
      ) : (
        <EmptyAssetCollection
          title={__('pim_asset_manager.asset_collection.no_asset_in_collection')}
          readonly={readonly}
        >
          <AssetsIllustration size={80} />
          <Label>{__('pim_asset_manager.asset_collection.no_asset_in_collection')}</Label>
        </EmptyAssetCollection>
      )}
    </Container>
  );
};
