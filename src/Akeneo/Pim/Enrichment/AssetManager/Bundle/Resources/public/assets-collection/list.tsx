import * as React from 'react';
import {connect} from 'react-redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {selectAttributeList, Attribute, getAttributeLabel} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {selectContext, ContextState, ChannelCode, LocaleCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {ValueCollection, Value, selectCurrentValues, AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
// import AssetSelector from 'akeneoassetmanager/application/component/app/asset-selector';
// import {createIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
// import {createChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
// import {createLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';
import styled from 'styled-components';
import {ThemedProps} from 'akeneopimenrichmentassetmanager/product/tab/asset';
import __ from 'akeneoreferenceentity/tools/translator';
import {fetchAssetByCodes, AssetFamilyIdentifier, Asset, getImage, isComplete} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/asset';
import {getLabel} from 'pimui/js/i18n';

type ListProps = {
  attributes: Attribute[],
  values: ValueCollection,
  context: ContextState
}

const SectionTitle = styled.div`
  display: flex;
  padding: 12px 0;
  align-items: center; //Should be baseline but the alignment is then very weird
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey140}
`;

const AttributeBreadCrumb = styled.div`
  font-size: 15px;
  font-weight: normal;
  text-transform: uppercase;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140}
`;

const IncompleteIndicator = styled.div`
  display: flex;
`;

const Spacer = styled.div`
  flex: 1;
`;

const AssetCounter = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
`;

const Separator = styled.div`
  border-left: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey100};
  margin: 0 10px;
  height: 24px;
`;

type ButtonProps = {
  buttonSize: 'micro' | 'medium' | 'default',
  color: 'green' | 'blue' | 'red' | 'grey' | 'outline'
}

const Button = styled.div`
  padding: 0 ${(props: ThemedProps<ButtonProps>) => 'micro' === props.buttonSize ? '10xp' : '15px'};
  height: ${(props: ThemedProps<ButtonProps>) => {
    switch (props.buttonSize) {
      case 'micro':
        return '20px';
      case 'medium':
        return '24px';
      default:
        return '32px';
    }
  }};
  line-height: ${(props: ThemedProps<ButtonProps>) => 'micro' === props.buttonSize ? '19xp' : '23px'};
  border-radius: ${(props: ThemedProps<ButtonProps>) => 'micro' === props.buttonSize ? '10xp' : '16px'};
  font-size: ${(props: ThemedProps<ButtonProps>) => 'micro' === props.buttonSize ? props.theme.fontSize.small : props.theme.fontSize.default};
  minimum-width: ${(props: ThemedProps<ButtonProps>) => 'micro' === props.buttonSize ? '60px' : '100px'};
  color: ${(props: ThemedProps<ButtonProps>) => 'outline' !== props.color ? 'white' : props.theme.color.grey120};
  background-color: ${(props: ThemedProps<ButtonProps>) => 'outline' !== props.color ? (props.theme.color as any)[props.color + '100'] : 'white'};
  cursor: pointer;
  text-transform: uppercase;
  border: 1px solid ${(props: ThemedProps<ButtonProps>) => 'outline' !== props.color ? 'transparent' : props.theme.color.grey80};
`;

const Pill = styled.div`
  background-color: ${(props: ThemedProps<void>) => props.theme.color.yellow100}
  width: 8px;
  min-width: 8px; // to fix a glitch on chrome when the pill is smashed
  height: 8px;
  border-radius: 8px;
  margin: 0 6px;
  align-self: center;
`

const Label = styled.div`
  color: ${(props: ThemedProps<{small: boolean, grey: boolean}>) => props.theme.color.grey120};
  font-size: ${(props: ThemedProps<{small: boolean, grey: boolean}>) => props.theme.fontSize.default};
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;

  ::first-letter {
    text-transform: capitalize
  }
`

const Thumbnail = ({asset}: {asset: Asset}) => {
  const Img = styled.img`
    width: 140px;
    height: 140px;
    border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey100}
  `;

  return (<Img src={getImage(asset)} />)
}

type AssetCollectionProps = {
  assetFamilyIdentifier: AssetFamilyIdentifier
  assetCodes: AssetCode[],
  context: {channel: ChannelCode, locale: LocaleCode}
}
const AssetCollection = ({assetFamilyIdentifier, assetCodes, context}: AssetCollectionProps) => {
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

  const AssetCard = styled.div`
    display: flex;
    flex-direction: column;
    height: 165px;
    margin-top: 10px;
    justify-content: space-between;
    margin-right: 20px;
  `;
  const Container = styled.div`
    display: flex;
  `;

  const AssetTitle = styled.div`
    display: flex;
    width: 140px;
    align-items: baseline;
  `;

  const BaselinePill = styled(Pill)`
    align-self: unset;
  `;

  return (
    <Container>
      {assets.map((asset: Asset) => (
        <AssetCard>
          <Thumbnail asset={asset} />
          <AssetTitle>
            <Label>
              {getLabel(asset.labels, context.locale, asset.code)}
            </Label>
            {!isComplete(asset) ? <BaselinePill /> : null}
          </AssetTitle>
        </AssetCard>
      ))}
    </Container>
  )
}

const List = ({values, context}: ListProps) => {
  return (
    <React.Fragment>
      {values.map((value: Value) => (
        <React.Fragment key={value.attribute.code}>
          <SectionTitle>
            <AttributeBreadCrumb>
              {value.attribute.group} / {getAttributeLabel(value.attribute, context.locale)}
            </AttributeBreadCrumb>
            <IncompleteIndicator>
              <Pill />
              <Label small grey>{__('pim_asset_manager.attribute.is_required')}</Label>
            </IncompleteIndicator>
            <Spacer />
            <AssetCounter>
              {__('pim_asset_manager.asset_collection.asset_count', {count: value.data.length})}
            </AssetCounter>
            <Separator />
            <Button buttonSize='medium' color='outline'>{__('pim_asset_manager.asset_collection.add_asset')}</Button>
          </SectionTitle>
          <div>
            <AssetCollection assetFamilyIdentifier={value.attribute.reference_data_name} assetCodes={value.data} context={context} />
          </div>
        </React.Fragment>
      ))}
    </React.Fragment>
  )
};

export default connect((state: AssetCollectionState): ListProps => ({
  attributes: selectAttributeList(state),
  context: selectContext(state),
  values: selectCurrentValues(state)
}))(List);


