import * as React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import __ from 'akeneoassetmanager/tools/translator';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import FilterCollection, {useFilterViews} from 'akeneoassetmanager/application/component/asset/list/filter-collection';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import Basket from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/basket';
import SearchBar from 'akeneoassetmanager/application/component/asset/list/search-bar';
import fetchAllChannels from 'akeneoassetmanager/infrastructure/fetcher/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import attributeFetcher from 'akeneoassetmanager/infrastructure/fetcher/attribute';
import {CloseButton} from 'akeneoassetmanager/application/component/app/close-button';
import {LabelCollection} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {getLabel} from 'pimui/js/i18n';
import {getAttributeLabel, Attribute as ProductAttribute} from 'akeneoassetmanager/platform/model/structure/attribute';
import {
  ScrollableModal,
  Header,
  Title,
  SubTitle,
  ConfirmButton,
} from 'akeneoassetmanager/application/component/app/modal';
import ListAsset, {
  canAddAssetToCollection,
  addAssetsToCollection,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import MosaicResult from 'akeneoassetmanager/application/component/asset/list/mosaic';
import {useFetchResult} from 'akeneoassetmanager/application/hooks/grid';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {useShortcut, Key, useSelection} from 'akeneo-design-system';

type AssetFamilyIdentifier = string;
type AssetPickerProps = {
  excludedAssetCollection: AssetCode[];
  assetFamilyIdentifier: AssetFamilyIdentifier;
  initialContext: Context;
  productLabels: LabelCollection;
  productAttribute: ProductAttribute;
  onAssetPick: (assetCodes: AssetCode[]) => void;
};

const Container = styled.div`
  display: flex;
  flex: 1;
  overflow-x: hidden;
`;
const Context = styled.div``;
const Grid = styled.div`
  display: flex;
  flex-direction: column;
  flex: 1;
  height: 100%;
  margin: 0 40px;
`;

const FilterContainer = styled.div`
  display: flex;
  flex-shrink: 0;
  flex-direction: column;
  width: 300px;
  padding-right: 20px;
  padding-left: 30px;
  border-right: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  overflow-y: auto;
  height: 100%;
`;

const FilterTitle = styled.div`
  padding-bottom: 10px;
  padding-top: 4px;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey100};
  text-transform: uppercase;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  background-color: white;
`;

const createQuery = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  filters: Filter[],
  searchValue: string,
  excludedAssetCollection: AssetCode[],
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
      {
        field: 'code',
        operator: 'NOT IN',
        value: excludedAssetCollection,
        context: {},
      },
    ],
  };
};

const dataProvider = {
  assetFetcher: {
    fetchByCode: (assetFamilyIdentifier: AssetFamilyIdentifier, assetCodeCollection: AssetCode[], context: Context) => {
      return assetFetcher.fetchByCodes(assetFamilyIdentifier, assetCodeCollection, context);
    },
    search: (query: Query) => {
      return assetFetcher.search(query);
    },
  },
  channelFetcher: {
    fetchAll: fetchAllChannels,
  },
  assetAttributeFetcher: {
    fetchAll: attributeFetcher.fetchAllNormalized,
  },
};

export const AssetPicker = ({
  assetFamilyIdentifier,
  initialContext,
  onAssetPick,
  excludedAssetCollection,
  productLabels,
  productAttribute,
}: AssetPickerProps) => {
  const [isOpen, setOpen] = React.useState(false);
  const [filterCollection, setFilterCollection] = React.useState<Filter[]>([]);

  const [searchValue, setSearchValue] = React.useState<string>('');
  const [searchResult, setSearchResult] = React.useState<SearchResult<ListAsset> | null>(null);
  const [context, setContext] = React.useState<Context>(initialContext);
  const [selection, selectionState, isItemSelected, onSelectionChange, onSelectAllChange] = useSelection<AssetCode>(
    null === searchResult ? 0 : searchResult.matchesCount
  );

  const resetModal = () => {
    onSelectAllChange(false);
    setSearchValue('');
    setFilterCollection([]);
    setOpen(false);
  };
  const cancelModal = () => {
    if (!isOpen) return;
    onAssetPick([]);
    resetModal();
  };

  useFetchResult(createQuery)(
    isOpen,
    dataProvider,
    assetFamilyIdentifier,
    filterCollection,
    searchValue,
    excludedAssetCollection,
    context,
    setSearchResult
  );
  const filterViews = useFilterViews(assetFamilyIdentifier, dataProvider);
  const canAddAsset = canAddAssetToCollection(addAssetsToCollection(excludedAssetCollection, selection.collection));

  useShortcut(Key.Escape, cancelModal);

  return (
    <React.Fragment>
      <Button
        title={__('pim_asset_manager.asset_collection.add_asset_title')}
        buttonSize="medium"
        color="outline"
        isDisabled={!canAddAsset}
        onClick={() => setOpen(true)}
      >
        {__('pim_asset_manager.asset_collection.add_asset')}
      </Button>
      {isOpen && null !== filterViews && null !== searchResult ? (
        <ScrollableModal data-container="asset-picker">
          <Header>
            <CloseButton title={__('pim_asset_manager.close')} onClick={cancelModal} />
            <Title>{__('pim_asset_manager.asset_picker.title')}</Title>
            <SubTitle>
              {__('pim_asset_manager.asset_picker.sub_title', {
                productLabel: getLabel(productLabels, context.locale, ''),
                attributeLabel: getAttributeLabel(productAttribute, context.locale),
              })}
            </SubTitle>
            <ConfirmButton
              title={__('pim_common.confirm')}
              color="green"
              onClick={() => {
                onAssetPick(selection.collection);
                resetModal();
              }}
            >
              {__('pim_common.confirm')}
            </ConfirmButton>
          </Header>
          <Container>
            {filterViews.length !== 0 && (
              <FilterContainer data-container="filter-collection">
                <FilterTitle>{__('pim_asset_manager.asset_picker.filter.title')}</FilterTitle>
                <FilterCollection
                  filterCollection={filterCollection}
                  context={context}
                  onFilterCollectionChange={(filterCollection: Filter[]) => {
                    setFilterCollection(filterCollection);
                  }}
                  orderedFilterViews={filterViews}
                />
              </FilterContainer>
            )}
            <Grid>
              <SearchBar
                dataProvider={dataProvider}
                searchValue={searchValue}
                context={context}
                resultCount={searchResult.matchesCount}
                onSearchChange={setSearchValue}
                onContextChange={setContext}
              />
              <MosaicResult
                assetCollection={searchResult.items}
                context={context}
                selectionState={selectionState}
                onSelectionChange={onSelectionChange}
                isItemSelected={isItemSelected}
                resultCount={searchResult.matchesCount}
                hasReachMaximumSelection={!canAddAsset}
              />
            </Grid>
            <Basket
              dataProvider={dataProvider}
              selection={selection.collection}
              assetFamilyIdentifier={assetFamilyIdentifier}
              context={context}
              onRemove={(assetCode: AssetCode) => {
                onSelectionChange(assetCode, false);
              }}
              onRemoveAll={() => {
                onSelectAllChange(false);
              }}
            />
          </Container>
        </ScrollableModal>
      ) : null}
    </React.Fragment>
  );
};
