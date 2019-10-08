import * as React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoreferenceentity/tools/translator';
import {Asset} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
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
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'web/bundles/akeneoassetmanager/domain/model/channel';
import {Query} from 'web/bundles/akeneoassetmanager/domain/fetcher/fetcher';
import attributeFetcher from 'akeneoassetmanager/infrastructure/fetcher/attribute';

type AssetFamilyIdentifier = string;
type AssetPickerProps = {
  excludedAssetCollection: AssetCode[];
  assetFamilyIdentifier: AssetFamilyIdentifier;
  initialContext: Context;
  onAssetPick: (assetCodes: AssetCode[]) => void;
};

const Modal = styled.div`
  display: flex;
  flex-direction: column;
  border-radius: 0;
  border: none;
  top: 0;
  left: 0;
  position: fixed;
  z-index: 1050;
  background: white;
  width: 100%;
  height: 100%;
  padding: 40px;
`;

const ConfirmButton = styled(Button)`
  position: absolute;
  top: 0;
  right: 0;
`;

const Title = styled.div`
  margin-bottom: 14px;
  width: 100%;
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  line-height: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  text-align: center;
`;
const SubTitle = styled.div`
  width: 100%
  text-align: center;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
  margin-bottom: 10px;
`;

const Header = styled.div`
  position: relative;
`;
const Container = styled.div`
  display: flex;
  flex: 1;
  height: 100%;
`;
const Context = styled.div``;
const Grid = styled.div`
  flex: 1;
  height: 100%;
  margin: 0 40px;
`;

export type FilterViewCollection = {
  view: FilterView;
  attribute: Attribute;
}[];
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

export const AssetPicker = ({
  assetFamilyIdentifier,
  initialContext,
  onAssetPick,
  excludedAssetCollection,
}: AssetPickerProps) => {
  const [isOpen, setOpen] = React.useState(false);
  const [filterCollection, setFilterCollection] = React.useState<Filter[]>([]);
  const [selection, setSelection] = React.useState<AssetCode[]>([]);
  const [searchValue, setSearchValue] = React.useState<string>('');
  const [resultCount, setResultCount] = React.useState<number | null>(null);
  const [resultCollection, setResultCollection] = React.useState<Asset[]>([]);
  const [context, setContext] = React.useState<Context>(initialContext);

  React.useEffect(() => {
    const query = createQuery(
      assetFamilyIdentifier,
      filterCollection,
      searchValue,
      excludedAssetCollection,
      context.channel,
      context.locale,
      0,
      100
    );
    dataProvider.assetFetcher.search(query).then((searchResult: any) => {
      setResultCollection(searchResult.items);
      setResultCount(searchResult.matchesCount);
    });
  }, [filterCollection, searchValue, context, excludedAssetCollection]);

  return (
    <React.Fragment>
      <Button
        buttonSize="medium"
        color="outline"
        onClick={() => {
          setSelection([]);
          setOpen(true);
        }}
      >
        {__('pim_asset_manager.asset_collection.add_asset')}
      </Button>
      {isOpen ? (
        <Modal>
          <Header>
            <Title>{__('pim_asset_manager.asset_picker.title')}</Title>
            <SubTitle>{__('pim_asset_manager.asset_picker.sub_title')}</SubTitle>
            <ConfirmButton
              color="green"
              onClick={() => {
                onAssetPick(selection);
                setOpen(false);
              }}
            >
              {__('pim_common.confirm')}
            </ConfirmButton>
          </Header>
          <Container>
            <FilterCollection
              dataProvider={dataProvider}
              filterViewsProvider={{getFilterViews}}
              filterCollection={filterCollection}
              assetFamilyIdentifier={assetFamilyIdentifier}
              context={context}
              onFilterCollectionChange={(filterCollection: Filter[]) => {
                setFilterCollection(filterCollection);
              }}
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
