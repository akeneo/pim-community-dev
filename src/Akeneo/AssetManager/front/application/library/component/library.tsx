import * as React from 'react';
import styled from 'styled-components';
import SearchBar from 'akeneoassetmanager/application/component/asset/list/search-bar';
import Mosaic from 'akeneoassetmanager/application/component/asset/list/mosaic';
import fetchAllChannels from 'akeneoassetmanager/infrastructure/fetcher/channel';
import {Context} from 'akeneoassetmanager/domain/model/context';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import {useFetchResult, useStoredState} from 'akeneoassetmanager/application/library/hooks/grid';
import FilterCollection, {useFilterViews} from 'akeneoassetmanager/application/component/asset/list/filter-collection';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoassetmanager/tools/translator';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import {AssetFamilySelector} from 'akeneoassetmanager/application/library/component/asset-family-selector';

const Column = styled.div`
  padding: 30px;
  flex-basis: 280px;
  width: 280px;
  position: relative;
  transition: flex-basis 0.3s ease-in-out, width 0.3s ease-in-out;
  order: -10;
  background: ${(props: ThemedProps<void>) => props.theme.color.grey60};
  border-right: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  flex-shrink: 0;
  height: 100%;
  z-index: 802;
  overflow: hidden;
`;

const ColumnTitle = styled.div`
  display: block;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey100};
  text-transform: uppercase;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  white-space: nowrap;
  margin-bottom: 3px;
`;

const Content = styled.div`
  flex: 1;
`;
const Header = styled.div``;
const Container = styled.div`
  display: flex;
  flex: 1;
  height: 100%;
`;
const Grid = styled.div`
  display: flex;
  flex-direction: column;
  flex: 1;
  height: 100%;
  margin: 0 40px;
`;
const dataProvider = {
  assetFetcher,
  channelFetcher: {
    fetchAll: fetchAllChannels,
  },
  assetFamilyFetcher,
};

const createQuery = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  filters: Filter[],
  searchValue: string,
  _excludedAssetCollection: AssetCode[],
  channel: ChannelCode,
  locale: LocaleCode,
  page: number,
  size: number
): Query => {
  return {
    locale,
    channel,
    size,
    page,
    filters: [
      ...filters,
      {
        field: 'asset_family',
        operator: '=',
        value: assetFamilyIdentifier,
        context: {},
      },
      {
        field: 'full_text',
        operator: '=',
        value: searchValue,
        context: {},
      },
    ],
  };
};

type LibraryProps = {
  initialContext: Context;
  redirectToAsset: (assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) => void;
};

const Library = ({initialContext, redirectToAsset}: LibraryProps) => {
  const [currentAssetFamily, setCurrentAssetFamily] = useStoredState<AssetFamilyIdentifier | null>(
    'akeneo.asset_manager.grid.current_asset_family',
    null,
    newAssetFamily => {
      if (null !== newAssetFamily) {
        // We need to reload the filters from local storage after changing the current asset family
        loadFilterCollectionFromStorage(`akeneo.asset_manager.grid.filter_collection_${newAssetFamily}`);
      }
    }
  );
  const [filterCollection, setFilterCollection, loadFilterCollectionFromStorage] = useStoredState<Filter[]>(
    `akeneo.asset_manager.grid.filter_collection_${currentAssetFamily}`,
    []
  );
  const [excludedAssetCollection] = React.useState<AssetCode[]>([]);
  const [selection, setSelection] = React.useState<AssetCode[]>([]);
  const [searchValue, setSearchValue] = useStoredState<string>('akeneo.asset_manager.grid.search_value', '');
  const [resultCount, setResultCount] = React.useState<number | null>(null);
  const [resultCollection, setResultCollection] = React.useState<ListAsset[]>([]);
  const [context, setContext] = useStoredState<Context>('akeneo.asset_manager.grid.context', initialContext);

  useFetchResult(createQuery)(
    true,
    dataProvider,
    currentAssetFamily,
    filterCollection,
    searchValue,
    excludedAssetCollection,
    context,
    setResultCollection,
    setResultCount
  );
  const filterViews = useFilterViews(currentAssetFamily, dataProvider);

  return (
    <Container>
      <Column>
        <ColumnTitle>{__('pim_asset_manager.asset_family.column_title')}</ColumnTitle>
        <AssetFamilySelector
          assetFamilyIdentifier={currentAssetFamily}
          locale={context.locale}
          dataProvider={dataProvider}
          onChange={setCurrentAssetFamily}
        />
        <FilterCollection
          filterCollection={filterCollection}
          context={context}
          onFilterCollectionChange={(filterCollection: Filter[]) => {
            setFilterCollection(filterCollection);
          }}
          orderedFilterViews={null === filterViews ? [] : filterViews}
        />
      </Column>
      <Content>
        <Header></Header>
        <Grid>
          <SearchBar
            dataProvider={dataProvider}
            searchValue={searchValue}
            context={context}
            resultCount={resultCount}
            onSearchChange={setSearchValue}
            onContextChange={setContext}
          />
          <Mosaic
            selection={selection}
            assetCollection={resultCollection}
            context={context}
            resultCount={resultCount}
            hasReachMaximumSelection={false}
            onSelectionChange={setSelection}
            onAssetClick={(assetCode: AssetCode) => {
              if (null !== currentAssetFamily) {
                redirectToAsset(currentAssetFamily, assetCode);
              }
            }}
          />
        </Grid>
      </Content>
    </Container>
  );
};

export default Library;
