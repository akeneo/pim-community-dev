import * as React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoreferenceentity/tools/translator';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {Asset} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {Filter, Query} from 'akeneoassetmanager/application/reducer/grid';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';

type AssetFamilyIdentifier = string;
type AssetPickerProps = {
  excludedAssetCollection: AssetCode[];
  assetFamilyIdentifier: AssetFamilyIdentifier;
  initialContext: Context;
  onAssetPick: (assetCodes: AssetCode[]) => void;
};

const Modal = styled.div`
  border-radius: 0;
  border: none;
  top: 0;
  left: 0;
  position: fixed;
  z-index: 1050;
  background: white;
  width: 100%;
  height: 100%;
`;

const ConfirmButton = styled(Button)`
  width: 120px;
  height: 32px;
  text-align: center;
  position: absolute;
  top: 40px;
  right: 40px;
  line-height: 30px;
  font-size: 15px;
`;

const Title = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  font-size: 36px;
  height: 44px;
  text-align: center;
  width: 100%;
  margin: 40px auto;
`;

const Header = styled.div``;
const Container = styled.div``;
const FilterCollection = styled.div<any>``;
const Basket = styled.div<any>``;
const Search = styled.div<any>``;
const ResultCount = styled.div<any>``;
const Context = styled.div<any>``;
const MosaicResult = styled.div<any>``;

export const AssetPicker = ({assetFamilyIdentifier, initialContext, onAssetPick}: AssetPickerProps) => {
  const [isOpen, setOpen] = React.useState(false);
  const [filterCollection, setFilterCollection] = React.useState<Filter[]>([]);
  const [selection, setSelection] = React.useState<AssetCode[]>([]);
  const [searchValue, setSearchValue] = React.useState<string>('');
  const [resultCount] = React.useState<number|null>(null);
  const [resultCollection] = React.useState<Asset[]>([]);
  const [context, setContext] = React.useState<Context>(initialContext);

  const dataProvider = {
    assetFetcher: {
      fetchByCode: (_assetFamilyIdentifier: AssetFamilyIdentifier, _assetCodeCollection: AssetCode[]) => {
        return new Promise((resolve) => resolve([]))
      },
      search: (_query: Query) => {
        return new Promise((resolve) => resolve({}))
      }
    },
    assetFamilyFetcher: {
      fetch: (_assetFamilyIdentifier: AssetFamilyIdentifier) => {
        return new Promise((resolve) => resolve({}))
      }
    },
    channelFetcher: {
      fetchAll: () => {
        return new Promise((resolve) => resolve([]))
      }
    }
  };

  return (
    <React.Fragment>
      <Button buttonSize='medium' color='outline' onClick={() => setOpen(true)}>{__('pim_asset_manager.asset_collection.add_asset')}</Button>
      { isOpen ? (
        <Modal>
          <Header>
            <Title>{__('pim_asset_manager.asset_picker.title')}</Title>
            <ConfirmButton
              buttonSize='medium'
              color='green'
              onClick={() => {
                onAssetPick([]);
                setOpen(false);
              }}
            >
              {__('pim_common.confirm')}
            </ConfirmButton>
          </Header>
          <Container>
            <FilterCollection
              dataProvider={dataProvider}
              filterCollection={filterCollection}
              assetFamilyIdentifier={assetFamilyIdentifier}
              context={context}
              onFilterCollectionChange={(filterCollection: Filter[]) => {
                setFilterCollection(filterCollection)
              }}
            />
            <div>
              <div>
                <Search
                  searchValue={searchValue}
                  onSearchChange={(newSearchValue: string) => {
                    setSearchValue(newSearchValue)
                  }}
                />
                <ResultCount
                  resultCount={resultCount}
                />
                <Context
                  dataProvider={dataProvider}
                  locale={context.locale}
                  onLocaleChange={(locale: LocaleCode) => {
                    setContext({...context, locale})
                  }}
                  channel={context.channel}
                  onChannelChange={(channel: ChannelCode) => {
                    setContext({...context, channel})
                  }}
                />
              </div>
              <MosaicResult
                selection={selection}
                result={resultCollection}
                context={context}
              />
            </div>
            <Basket
              dataProvider={dataProvider}
              selection={selection}
              assetFamilyIdentifier={assetFamilyIdentifier}
              context={context}
              onSelectionChange={(assetCodeCollection: AssetCode[]) => {
                setSelection(assetCodeCollection)
              }}
            />
          </Container>
        </Modal>
      ) : null }
    </React.Fragment>
  );
};
