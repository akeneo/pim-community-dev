import * as React from 'react'
import {AssetFamilyIdentifier, Asset, getImage, isComplete, emptyAsset} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {ChannelCode} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
import {LocaleCode} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import styled from 'styled-components';
import {Pill} from 'akeneopimenrichmentassetmanager/platform/component/common';
import {ThemedProps} from 'akeneopimenrichmentassetmanager/platform/component/theme';
import {getLabel} from 'pimui/js/i18n';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';
import AssetIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/illustration/asset';
import __ from 'akeneoreferenceentity/tools/translator';
import {fetchAssetByCodes} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/asset';

const AssetCard = styled.div<{readonly: boolean}>`
  display: flex;
  flex-direction: column;
  height: 165px;
  margin-top: 10px;
  justify-content: space-between;
  margin-right: 20px;
  opacity: ${(props: ThemedProps<{readonly: boolean}>) => props.readonly ? .8 : 1}
`;
const Container = styled.div`
  display: flex;
  flex-wrap: wrap;
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


const Thumbnail = ({asset}: {asset: Asset}) => {
  const Img = styled.img`
    width: 140px;
    height: 140px;
    border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey100}
  `;

  return (<Img src={getImage(asset)}/>)
}

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

type AssetCollectionProps = {
  assetFamilyIdentifier: AssetFamilyIdentifier
  assetCodes: AssetCode[],
  readonly: boolean
  context: {channel: ChannelCode, locale: LocaleCode}
}

export const AssetCollection = ({assetFamilyIdentifier, assetCodes, readonly, context}: AssetCollectionProps) => {
  const [assets, assetsReceived] = React.useState<Asset[]>([]);
  const noChangeInCollection = (assetCodes: AssetCode[], assets: Asset[]) => {
    return assets.length !== assetCodes.length || !assets.sort().every((asset: Asset, index: number) => assetCodes.sort().indexOf(asset.code) === index )
  }
  React.useEffect(() => {
    if (assetCodes.length !== 0 && (noChangeInCollection(assetCodes, assets))) {
      fetchAssetByCodes(assetFamilyIdentifier, assetCodes, context).then((receivedAssets: Asset[]) => {
        assetsReceived(receivedAssets);
      })
    }
  });

  return (
    <Container>
      {/* Collection is not empty and is loaded */}
      {0 !== assets.length ? (
        <React.Fragment>
          {assets.map((asset: Asset) => (
            <AssetCard key={asset.code} readonly={readonly}>
              <Thumbnail asset={asset} />
              <AssetTitle>
                <Label>
                  {getLabel(asset.labels, context.locale, asset.code)}
                </Label>
                {!isComplete(asset) ? <BaselinePill /> : null}
              </AssetTitle>
            </AssetCard>
          ))}
        </React.Fragment>
      ) : null}
      {/* Collection is not empty and is not loaded */}
      {0 === assets.length && 0 !== assetCodes.length ? (
        <React.Fragment>
          {assetCodes.map((assetCode: AssetCode) => (
            <AssetCard key={assetCode} className='AknLoadingPlaceHolderContainer' readonly={false}>
              <Thumbnail asset={emptyAsset()}/>
              <AssetTitle />
            </AssetCard>
          ))}
        </React.Fragment>
      ) : null}
      {/* Collection is empty */}
      {0 === assetCodes.length ? (
        <EmptyAssetCollection>
          <AssetIllustration size={80}/>
          <Label>{__('pim_asset_manager.asset_collection.no_asset')}</Label>
        </EmptyAssetCollection>
      ): null}
    </Container>
  )
}
