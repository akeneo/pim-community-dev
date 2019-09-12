import * as React from 'react'
import {AssetFamilyIdentifier} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset-family';
import {Asset, isComplete, emptyAsset, getAssetLabel} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import styled from 'styled-components';
import {Pill} from 'akeneopimenrichmentassetmanager/platform/component/common';
import {ThemedProps} from 'akeneopimenrichmentassetmanager/platform/component/theme';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';
import AssetIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/illustration/asset';
import __ from 'akeneoreferenceentity/tools/translator';
import {fetchAssetCollection} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/asset';
import {ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {Thumbnail} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection/thumbnail';

const AssetCard = styled.div<{readonly: boolean}>`
  display: flex;
  flex-direction: column;
  height: 165px;
  margin-top: 10px;
  justify-content: space-between;
  margin-right: 20px;
  opacity: ${(props: ThemedProps<{readonly: boolean}>) => props.readonly ? .8 : 1}
`;

const AssetTitle = styled.div`
  display: flex;
  width: 140px;
  align-items: baseline;
  min-height: 15px
`;

const BaselinePill = styled(Pill)`
  align-self: unset;
`;

const EmptyAssetCollection = styled.div`
  height: 140px;
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  padding: 20px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  margin: 10px 0;
`

const Container = styled.div`
  display: flex;
  flex-wrap: wrap;
`;

type AssetCollectionProps = {
  assetFamilyIdentifier: AssetFamilyIdentifier
  assetCodes: AssetCode[],
  readonly: boolean
  context: ContextState
  onChange: (value: AssetCode[]) => void
}

const useLoadAssets = (assetCodes: AssetCode[], assetFamilyIdentifier: AssetFamilyIdentifier, context: ContextState) => {
  const [assets, assetsReceived] = React.useState<Asset[]>([]);
  const hasChangeInCollection = (assetCodes: AssetCode[], assets: Asset[]) => {
    return !(assets.length !== assetCodes.length || !assets.sort().every((asset: Asset, index: number) => assetCodes.sort().indexOf(asset.code) === index))
  }

  React.useEffect(() => {
    if (assetCodes.length !== 0 && (hasChangeInCollection(assetCodes, assets))) {
      fetchAssetCollection(assetFamilyIdentifier, assetCodes, context).then((receivedAssets: Asset[]) => {
        assetsReceived(receivedAssets);
      })
    }
  });

  return assets;
}

export const AssetCollection = ({assetFamilyIdentifier, assetCodes, readonly, context, onChange}: AssetCollectionProps) => {
  const assets = useLoadAssets(assetCodes, assetFamilyIdentifier, context);

  return (
    <Container>
      {/* Collection is not empty and is loaded (we also need to check assetCodes because in this case we don't update the fetched assets */}
      {0 !== assetCodes.length ? (
        <React.Fragment>
          {assetCodes.map((assetCode: AssetCode) => {
            const asset = assets.find((asset: Asset) => asset.code === assetCode);

            if (undefined === asset) {
              return (
                <AssetCard key={assetCode} className='AknLoadingPlaceHolderContainer' readonly={false}>
                  <Thumbnail asset={emptyAsset()} context={context} readonly={true} onRemove={() => {}}/>
                  <AssetTitle />
                </AssetCard>
              )
            }

            return (
              <AssetCard key={asset.code} readonly={readonly}>
                <Thumbnail asset={asset} context={context} readonly={readonly} onRemove={() => {
                  onChange(assetCodes.filter((assetCode: AssetCode) => asset.code !== assetCode))
                }}/>
                <AssetTitle>
                  <Label>
                    {getAssetLabel(asset, context.locale)}
                  </Label>
                  {!isComplete(asset) ? <BaselinePill /> : null}
                </AssetTitle>
              </AssetCard>
            )
        })}
        </React.Fragment>
      ) : (
        <EmptyAssetCollection>
          <AssetIllustration size={80}/>
          <Label>{__('pim_asset_manager.asset_collection.no_asset_in_collection')}</Label>
        </EmptyAssetCollection>
      )}
    </Container>
  )
}
