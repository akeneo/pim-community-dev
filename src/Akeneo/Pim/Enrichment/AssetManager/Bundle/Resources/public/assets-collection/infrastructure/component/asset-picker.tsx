import * as React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoreferenceentity/tools/translator';
import {Asset} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {Filter, Query, createQuery} from 'akeneoassetmanager/application/reducer/grid';
import FilterCollection from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/filter-collection';
import MosaicResult from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/mosaic';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import Basket from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/basket';
import {hasDataFilterView, getDataFilterView, FilterView} from 'akeneoassetmanager/application/configuration/value';
import {NormalizedAttribute, Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import denormalizeAttribute from 'akeneoassetmanager/application/denormalizer/attribute/attribute';
import SearchBar from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/search-bar';
import fetchAllChannels from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/channel';

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
const getFilterViews = (attributes: NormalizedAttribute[]): FilterViewCollection => {
  const attributesWithFilterViews = attributes.filter(({type}: NormalizedAttribute) => hasDataFilterView(type));
  const filterViews = attributesWithFilterViews.map((attribute: NormalizedAttribute) => ({
    view: getDataFilterView(attribute.type),
    attribute: denormalizeAttribute(attribute),
  }));

  return filterViews;
};

export const AssetPicker = ({assetFamilyIdentifier, initialContext, onAssetPick}: AssetPickerProps) => {
  const [isOpen, setOpen] = React.useState(false);
  const [filterCollection, setFilterCollection] = React.useState<Filter[]>([]);
  const [selection, setSelection] = React.useState<AssetCode[]>([]);
  const [searchValue, setSearchValue] = React.useState<string>('');
  const [resultCount, setResultCount] = React.useState<number | null>(null);
  const [resultCollection, setResultCollection] = React.useState<Asset[]>([]);
  const [context, setContext] = React.useState<Context>(initialContext);

  React.useEffect(() => {
    dataProvider.assetFetcher.search(createQuery({filters: filterCollection})).then((searchResult: any) => {
      setResultCollection(searchResult.items);
      setResultCount(searchResult.matches_count);
    });
  }, []);

  const dataProvider = {
    assetFetcher: {
      fetchByCode: (assetFamilyIdentifier: AssetFamilyIdentifier, assetCodeCollection: AssetCode[]) => {
        return assetFetcher.fetchByCodes(assetFamilyIdentifier, assetCodeCollection, context);
      },
      search: (_query: Query) => {
        return new Promise(resolve =>
          resolve({
            items: [
              {
                identifier: 'packshot_Philips22PDL4906H_pa_e14f3b03-1929-4109-9b07-68e4f64bba74',
                asset_family_identifier: 'packshot',
                code: 'Philips22PDL4906H_pack',
                labels: [],
                image:
                  '/rest/asset_manager/image_preview/image_packshot_99e561de-5ec8-47ba-833c-42e150fe8b7f/thumbnail?data=',
                values: {
                  product_sku_packshot_fingerprint: {
                    data: '10638601',
                    locale: null,
                    channel: null,
                    attribute: 'product_sku_packshot_fingerprint',
                  },
                  date_published_packshot_fingerprint: {
                    data: '18/02/2018',
                    locale: null,
                    channel: null,
                    attribute: 'date_published_packshot_fingerprint',
                  },
                  linked_attribute_packshot_fingerprint: {
                    data: 'packshot',
                    locale: null,
                    channel: null,
                    attribute: 'linked_attribute_packshot_fingerprint',
                  },
                  description_packshot_fingerprint_en_US: {
                    data: 'Used technical ref only.',
                    locale: 'en_US',
                    channel: null,
                    attribute: 'description_packshot_fingerprint',
                  },
                },
                completeness: {complete: 2, required: 3},
              },
              {
                identifier: 'packshot_iphone8_pack_daadf101-ec94-43a1-8609-2fff24d21c39',
                asset_family_identifier: 'packshot',
                code: 'iphone8_pack',
                labels: [],
                image:
                  '/rest/asset_manager/image_preview/image_packshot_99e561de-5ec8-47ba-833c-42e150fe8b7f/thumbnail?data=',
                values: {
                  product_sku_packshot_fingerprint: {
                    data: 'apple_iphone_8',
                    locale: null,
                    channel: null,
                    attribute: 'product_sku_packshot_fingerprint',
                  },
                  date_published_packshot_fingerprint: {
                    data: '18/05/2017',
                    locale: null,
                    channel: null,
                    attribute: 'date_published_packshot_fingerprint',
                  },
                  linked_attribute_packshot_fingerprint: {
                    data: 'packshot',
                    locale: null,
                    channel: null,
                    attribute: 'linked_attribute_packshot_fingerprint',
                  },
                  description_packshot_fingerprint_en_US: {
                    data: 'You should probably buy it.',
                    locale: 'en_US',
                    channel: null,
                    attribute: 'description_packshot_fingerprint',
                  },
                  description_packshot_fingerprint_fr_FR: {
                    data: 'Vous devriez probablement l\u0027acheter.',
                    locale: 'fr_FR',
                    channel: null,
                    attribute: 'description_packshot_fingerprint',
                  },
                },
                completeness: {complete: 2, required: 3},
              },
              {
                identifier: 'packshot_iphone7_pack_9c35ba44-e4f9-4a48-8250-4c554e6704a4',
                asset_family_identifier: 'packshot',
                code: 'iphone7_pack',
                labels: [],
                image:
                  '/rest/asset_manager/image_preview/image_packshot_99e561de-5ec8-47ba-833c-42e150fe8b7f/thumbnail?data=',
                values: {
                  product_sku_packshot_fingerprint: {
                    data: 'apple_iphone_7',
                    locale: null,
                    channel: null,
                    attribute: 'product_sku_packshot_fingerprint',
                  },
                  date_published_packshot_fingerprint: {
                    data: '18/05/2017',
                    locale: null,
                    channel: null,
                    attribute: 'date_published_packshot_fingerprint',
                  },
                  linked_attribute_packshot_fingerprint: {
                    data: 'packshot',
                    locale: null,
                    channel: null,
                    attribute: 'linked_attribute_packshot_fingerprint',
                  },
                  description_packshot_fingerprint_en_US: {
                    data: 'You should probably buy it.',
                    locale: 'en_US',
                    channel: null,
                    attribute: 'description_packshot_fingerprint',
                  },
                  description_packshot_fingerprint_fr_FR: {
                    data: 'Vous devriez probablement l\u0027acheter.',
                    locale: 'fr_FR',
                    channel: null,
                    attribute: 'description_packshot_fingerprint',
                  },
                },
                completeness: {complete: 2, required: 3},
              },
            ],
            matches_count: 3,
            total_count: 3,
          })
        );
      },
    },
    assetFamilyFetcher: {
      fetch: (_assetFamilyIdentifier: AssetFamilyIdentifier) => {
        return new Promise(resolve => resolve({}));
      },
    },
    channelFetcher: {
      fetchAll: fetchAllChannels,
    },
    assetAttributesFetcher: {
      fetchAll: (_assetFamilyIdentifier: AssetFamilyIdentifier) => {
        return new Promise(resolve =>
          resolve([
            {
              type: 'text',
              identifier: 'label_packshot_8dcd3582-6375-48b2-a6a3-2f940089eb17',
              asset_family_identifier: 'packshot',
              code: 'label',
              labels: [],
              is_required: false,
              order: 12,
              value_per_locale: true,
              value_per_channel: false,
              max_length: null,
              is_textarea: false,
              validation_rule: 'none',
              regular_expression: null,
              is_rich_text_editor: false,
            },
            {
              type: 'image',
              identifier: 'image_packshot_99e561de-5ec8-47ba-833c-42e150fe8b7f',
              asset_family_identifier: 'packshot',
              code: 'image',
              labels: [],
              is_required: false,
              order: 1,
              value_per_locale: false,
              value_per_channel: false,
              max_file_size: null,
              allowed_extensions: [],
            },
            {
              type: 'option',
              identifier: 'shooted_by_packshot_fingerprint',
              asset_family_identifier: 'packshot',
              code: 'shooted_by',
              labels: {en_US: 'Shooted By'},
              is_required: true,
              order: 4,
              value_per_locale: false,
              value_per_channel: false,
              options: [],
            },
            {
              type: 'text',
              identifier: 'product_sku_packshot_fingerprint',
              asset_family_identifier: 'packshot',
              code: 'product_sku',
              labels: {en_US: 'Product SKU'},
              is_required: true,
              order: 8,
              value_per_locale: false,
              value_per_channel: false,
              max_length: null,
              is_textarea: false,
              validation_rule: 'regular_expression',
              regular_expression: '/^(\\w-?)*/',
              is_rich_text_editor: false,
            },
            {
              type: 'asset_collection',
              identifier: 'aedeafz_packshot_555f41cb-f6d4-4a86-ba01-97c4828e3fa2',
              asset_family_identifier: 'packshot',
              code: 'aedeafz',
              labels: {en_US: 'aedeafz'},
              is_required: false,
              order: 0,
              value_per_locale: false,
              value_per_channel: false,
              asset_type: 'notice',
            },
            {
              type: 'asset_collection',
              identifier: 'assetcool_packshot_3197188d-3533-4ad8-9a5b-0e67cb06d709',
              asset_family_identifier: 'packshot',
              code: 'assetcool',
              labels: {en_US: 'asset cool'},
              is_required: false,
              order: 10,
              value_per_locale: false,
              value_per_channel: false,
              asset_type: 'video_presentation',
            },
            {
              type: 'option_collection',
              identifier: 'optioncoll_packshot_a7c15ca9-c09d-4772-9baa-879751c1b18c',
              asset_family_identifier: 'packshot',
              code: 'optioncoll',
              labels: {en_US: 'optioncoll'},
              is_required: false,
              order: 11,
              value_per_locale: false,
              value_per_channel: false,
              options: [],
            },
          ])
        );
      },
    },
  };

  return (
    <React.Fragment>
      <Button buttonSize="medium" color="outline" onClick={() => setOpen(true)}>
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
