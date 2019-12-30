import * as React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import __ from 'akeneoassetmanager/tools/translator';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import FilterCollection from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/filter-collection';
import MosaicResult from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/mosaic';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import Basket from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/basket';
import {hasDataFilterView, getDataFilterView, FilterView} from 'akeneoassetmanager/application/configuration/value';
import {Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import SearchBar from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/search-bar';
import fetchAllChannels from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import attributeFetcher from 'akeneoassetmanager/infrastructure/fetcher/attribute';
import {CloseButton} from 'akeneoassetmanager/application/component/app/close-button';
import Key from 'akeneoassetmanager/tools/key';
import {LabelCollection} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {getLabel} from 'pimui/js/i18n';
import {
  getAttributeLabel,
  Attribute as ProductAttribute,
} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {OptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {OptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {AssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';
import {AssetCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset-collection';
import {Modal, Header, Title, SubTitle, ConfirmButton} from 'akeneoassetmanager/application/component/app/modal';
import ListAsset, {
  canAddAssetToCollection,
  addAssetsToCollection,
} from 'akeneoassetmanager/domain/model/asset/list-asset';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';

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
  height: 100%;
`;
const Context = styled.div``;
const Grid = styled.div`
  display: flex;
  flex-direction: column;
  flex: 1;
  height: 100%;
  margin: 0 40px;
`;

export type FilterViewCollection = {
  view: FilterView;
  attribute: Attribute;
}[];
export type FilterableAttribute =
  | OptionAttribute
  | OptionCollectionAttribute
  | AssetAttribute
  | AssetCollectionAttribute;

export const sortFilterViewsByAttributeOrder = (filterViewCollection: FilterViewCollection) => {
  return [...filterViewCollection].sort(
    (filterViewA, filterviewB) => filterViewA.attribute.order - filterviewB.attribute.order
  );
};

const getFilterViews = (attributes: Attribute[]): FilterViewCollection => {
  const attributesWithFilterViews = attributes.filter(({type}: Attribute) => hasDataFilterView(type));
  const filterViews = attributesWithFilterViews.map((attribute: Attribute) => ({
    view: getDataFilterView(attribute.type),
    attribute: attribute,
  }));

  return filterViews;
};

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
  assetAttributesFetcher: {
    fetchAll: (assetFamilyIdentifier: AssetFamilyIdentifier) => {
      return attributeFetcher.fetchAll(assetFamilyIdentifier);
    },
  },
};

const MAX_RESULT = 500;
const FIRST_PAGE_SIZE = 50;

let totalRequestCount = 0;
const useFetchResult = (
  isOpen: boolean,
  dataProvider: any,
  assetFamilyIdentifier: AssetFamilyIdentifier,
  filters: Filter[],
  searchValue: string,
  excludedAssetCollection: AssetCode[],
  context: Context,
  setResultCollection: (resultCollection: ListAsset[]) => void,
  setResultCount: (count: number) => void
) => {
  React.useEffect(() => {
    if (!isOpen) {
      return;
    }

    const query = createQuery(
      assetFamilyIdentifier,
      filters,
      searchValue,
      excludedAssetCollection,
      context.channel,
      context.locale,
      0,
      FIRST_PAGE_SIZE
    );
    totalRequestCount++;

    dataProvider.assetFetcher.search(query).then((searchResult: any) => {
      const currentRequestCount = totalRequestCount;
      setResultCollection(searchResult.items);
      setResultCount(searchResult.matchesCount);

      if (searchResult.matchesCount > FIRST_PAGE_SIZE) {
        fetchMoreResult(currentRequestCount)(query, setResultCollection);
      }
    });
  }, [filters, searchValue, context, excludedAssetCollection, isOpen]);
};

const fetchMoreResult = (currentRequestCount: number) => (
  query: Query,
  setResultCollection: (resultCollection: ListAsset[]) => void
) => {
  dataProvider.assetFetcher.search({...query, size: MAX_RESULT}).then((searchResult: any) => {
    if (currentRequestCount === totalRequestCount) {
      setResultCollection(searchResult.items);
    }
  });
};

const useFilterViews = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  dataProvider: any,
  getFilterViews: (attributes: Attribute[]) => FilterViewCollection
): FilterViewCollection | null => {
  const [filterViews, setFilterViews] = React.useState<FilterViewCollection | null>(null);

  React.useEffect(() => {
    dataProvider.assetAttributesFetcher.fetchAll(assetFamilyIdentifier).then((attributes: Attribute[]) => {
      setFilterViews(sortFilterViewsByAttributeOrder(getFilterViews(attributes)));
    });
  }, [assetFamilyIdentifier]);

  return filterViews;
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
  const [selection, setSelection] = React.useState<AssetCode[]>([]);
  const [searchValue, setSearchValue] = React.useState<string>('');
  const [resultCount, setResultCount] = React.useState<number | null>(null);
  const [resultCollection, setResultCollection] = React.useState<ListAsset[]>([]);
  const [context, setContext] = React.useState<Context>(initialContext);

  const resetModal = () => {
    setSelection([]);
    setSearchValue('');
    setFilterCollection([]);
    setOpen(false);
  };
  const cancelModal = () => {
    if (!isOpen) return;
    onAssetPick([]);
    resetModal();
  };

  useFetchResult(
    isOpen,
    dataProvider,
    assetFamilyIdentifier,
    filterCollection,
    searchValue,
    excludedAssetCollection,
    context,
    setResultCollection,
    setResultCount
  );
  const filterViews = useFilterViews(assetFamilyIdentifier, dataProvider, getFilterViews);

  React.useEffect(() => {
    const cancelModalOnEscape = (event: KeyboardEvent) => (Key.Escape === event.code ? cancelModal() : null);
    document.addEventListener('keydown', cancelModalOnEscape);

    return () => document.removeEventListener('keydown', cancelModalOnEscape);
  }, []);

  const canAddAsset = canAddAssetToCollection(addAssetsToCollection(excludedAssetCollection, selection));

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
      {isOpen && null !== filterViews ? (
        <Modal data-container="asset-picker">
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
                onAssetPick(selection);
                resetModal();
              }}
            >
              {__('pim_common.confirm')}
            </ConfirmButton>
          </Header>
          <Container>
            <FilterCollection
              filterCollection={filterCollection}
              context={context}
              onFilterCollectionChange={(filterCollection: Filter[]) => {
                setFilterCollection(filterCollection);
              }}
              orderedFilterViews={filterViews}
            />
            <Grid>
              <SearchBar
                dataProvider={dataProvider}
                searchValue={searchValue}
                context={context}
                resultCount={resultCount}
                onSearchChange={setSearchValue}
                onContextChange={setContext}
              />
              <MosaicResult
                selection={selection}
                assetCollection={resultCollection}
                context={context}
                resultCount={resultCount}
                hasReachMaximumSelection={!canAddAsset}
                onSelectionChange={(assetCodeCollection: AssetCode[]) => {
                  setSelection(assetCodeCollection);
                }}
              />
            </Grid>
            <Basket
              dataProvider={dataProvider}
              selection={selection}
              assetFamilyIdentifier={assetFamilyIdentifier}
              context={context}
              onSelectionChange={(assetCodeCollection: AssetCode[]) => {
                setSelection(assetCodeCollection);
              }}
            />
          </Container>
        </Modal>
      ) : null}
    </React.Fragment>
  );
};
