import React, {useState} from 'react';
import styled from 'styled-components';
import {Button, getColor, getFontSize, useSelection, Modal, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import FilterCollection, {useFilterViews} from 'akeneoassetmanager/application/component/asset/list/filter-collection';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import Basket from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/basket';
import {SearchBar} from 'akeneoassetmanager/application/component/asset/list/search-bar';
import fetchAllChannels from 'akeneoassetmanager/infrastructure/fetcher/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import attributeFetcher from 'akeneoassetmanager/infrastructure/fetcher/attribute';
import {LabelCollection} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {getLabel} from 'pimui/js/i18n';
import {getAttributeLabel, Attribute as ProductAttribute} from 'akeneoassetmanager/platform/model/structure/attribute';
import ListAsset, {
  canAddAssetToCollection,
  addAssetsToCollection,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import MosaicResult from 'akeneoassetmanager/application/component/asset/list/mosaic';
import {useFetchResult} from 'akeneoassetmanager/application/hooks/grid';

type AssetFamilyIdentifier = string;
type AssetPickerProps = {
  excludedAssetCollection: AssetCode[];
  assetFamilyIdentifier: AssetFamilyIdentifier;
  initialContext: Context;
  productLabels: LabelCollection;
  productAttribute: ProductAttribute;
  onAssetPick: (assetCodes: AssetCode[]) => void;
};

const FullModal = styled(Modal)`
  padding: 20px 0 0;
`;

const Container = styled.div`
  display: flex;
  flex: 1;
  overflow-x: hidden;
  width: 100%;
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
  border-right: 1px solid ${getColor('grey', 80)};
  overflow-y: auto;
  height: 100%;
`;

const FilterTitle = styled.div`
  padding-bottom: 10px;
  padding-top: 4px;
  color: ${getColor('grey', 100)};
  text-transform: uppercase;
  font-size: ${getFontSize('default')};
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

const AssetPicker = ({
  assetFamilyIdentifier,
  initialContext,
  onAssetPick,
  excludedAssetCollection,
  productLabels,
  productAttribute,
}: AssetPickerProps) => {
  const [isOpen, openPicker, closePicker] = useBooleanState(false);
  const [filterCollection, setFilterCollection] = useState<Filter[]>([]);
  const [searchValue, setSearchValue] = useState<string>('');
  const [searchResult, setSearchResult] = useState<SearchResult<ListAsset> | null>(null);
  const [context, setContext] = useState<Context>(initialContext);
  const [selection, selectionState, isItemSelected, onSelectionChange, onSelectAllChange] = useSelection<AssetCode>(
    null === searchResult ? 0 : searchResult.matchesCount
  );
  const translate = useTranslate();

  const resetModal = () => {
    onSelectAllChange(false);
    setSearchValue('');
    setFilterCollection([]);
    closePicker();
  };

  const handleConfirm = () => {
    onAssetPick(selection.collection);
    resetModal();
  };

  const handleCancel = () => {
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

  return (
    <>
      <Button
        title={translate('pim_asset_manager.asset_collection.add_asset_title')}
        size="small"
        level="tertiary"
        ghost={true}
        disabled={!canAddAsset}
        onClick={openPicker}
      >
        {translate('pim_asset_manager.asset_collection.add_asset')}
      </Button>
      {isOpen && null !== filterViews && null !== searchResult && (
        <FullModal closeTitle={translate('pim_common.close')} onClose={handleCancel} data-container="asset-picker">
          <Modal.Title>{translate('pim_asset_manager.asset_picker.title')}</Modal.Title>
          {translate('pim_asset_manager.asset_picker.sub_title', {
            productLabel: getLabel(productLabels, context.locale, ''),
            attributeLabel: getAttributeLabel(productAttribute, context.locale),
          })}
          <Modal.TopRightButtons>
            <Button onClick={handleConfirm}>{translate('pim_common.confirm')}</Button>
          </Modal.TopRightButtons>
          <Container>
            {filterViews.length !== 0 && (
              <FilterContainer data-container="filter-collection">
                <FilterTitle>{translate('pim_asset_manager.asset_picker.filter.title')}</FilterTitle>
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
        </FullModal>
      )}
    </>
  );
};

export {AssetPicker};
