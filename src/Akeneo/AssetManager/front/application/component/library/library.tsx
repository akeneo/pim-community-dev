import * as React from 'react';
import styled from 'styled-components';
import SearchBar from 'akeneoassetmanager/application/component/asset/list/search-bar';
import Mosaic from 'akeneoassetmanager/application/component/asset/list/mosaic';
import {Context} from 'akeneoassetmanager/domain/model/context';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import {useFetchResult} from 'akeneoassetmanager/application/hooks/grid';
import FilterCollection, {useFilterViews} from 'akeneoassetmanager/application/component/asset/list/filter-collection';
import __ from 'akeneoassetmanager/tools/translator';
import {AssetFamilySelector} from 'akeneoassetmanager/application/component/library/asset-family-selector';
import {HeaderView} from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {getLabel} from 'pimui/js/i18n';
import {MultipleButton, Button} from 'akeneoassetmanager/application/component/app/button';
import UploadModal from 'akeneoassetmanager/application/asset-upload/component/modal';
import {useAssetFamily} from 'akeneoassetmanager/application/hooks/asset-family';
import {CreateModal} from 'akeneoassetmanager/application/component/asset/create';
import {useNotify} from 'akeneoassetmanager/application/hooks/notify';
import {CreateAssetFamilyModal} from 'akeneoassetmanager/application/component/asset-family/create';
import {useRedirect} from 'akeneoassetmanager/application/hooks/router';
import {useStoredState} from 'akeneoassetmanager/application/hooks/state';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import {useChannels} from 'akeneoassetmanager/application/hooks/channel';
import DeleteModal from 'akeneoassetmanager/application/component/app/delete-modal';
import {deleteAllAssetFamilyAssets} from 'akeneoassetmanager/application/action/asset/delete';
import {
  HelperSection,
  HelperSeparator,
  HelperTitle,
  HelperText,
} from 'akeneoassetmanager/platform/component/common/helper';
import {NoDataSection, NoDataTitle, NoDataText} from 'akeneoassetmanager/platform/component/common/no-data';
import AssetIllustration from 'akeneoassetmanager/platform/component/visual/illustration/asset';
import {Link} from 'akeneoassetmanager/application/component/app/link';
import {ColumnTitle, Column} from 'akeneoassetmanager/application/component/app/column';
import AssetFetcher from 'akeneoassetmanager/domain/fetcher/asset';
import {ChannelFetcher} from 'akeneoassetmanager/application/hooks/channel';
import {AssetFamilyFetcher} from 'akeneoassetmanager/domain/fetcher/asset-family';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {clearImageLoadingQueue} from 'akeneoassetmanager/tools/image-loader';
import {getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';

const Header = styled.div`
  padding-left: 40px;
  padding-right: 40px;
  height: 136px;
`;

const Content = styled.div`
  flex: 1;
`;

const Container = styled.div`
  display: flex;
  flex: 1;
  height: 100%;
`;

const Grid = styled.div`
  display: flex;
  flex-direction: column;
  flex: 1;
  height: calc(100% - 136px);
  margin: 0 40px;
`;

const Buttons = styled.div`
  display: flex;
  > :not(:first-child) {
    margin-left: 10px;
  }
`;

const createQuery = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  filters: Filter[],
  searchValue: string,
  _excludedAssetCollection: AssetCode[],
  channel: ChannelCode,
  locale: LocaleCode,
  page: number,
  size: number
): Query => ({
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
});

const SecondaryActions = ({
  canDeleteAllAssets,
  onOpenDeleteAllAssetsModal,
}: {
  onOpenDeleteAllAssetsModal: () => void;
  canDeleteAllAssets: boolean;
}) => {
  if (!canDeleteAllAssets) return null;

  return (
    <div className="AknSecondaryActions AknDropdown AknButtonList-item">
      <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
      <div className="AknDropdown-menu AknDropdown-menu--right">
        <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
        <div>
          <button tabIndex={-1} className="AknDropdown-menuLink" onClick={onOpenDeleteAllAssetsModal}>
            {__('pim_asset_manager.asset.button.delete_all')}
          </button>
        </div>
      </div>
    </div>
  );
};

const useRoute = () => {
  const redirect = useRedirect();
  const redirectToAsset = React.useCallback(
    (assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) =>
      redirect('akeneo_asset_manager_asset_edit', {
        assetCode,
        assetFamilyIdentifier,
        tab: 'enrich',
      }),
    []
  );
  const redirectToAssetFamily = React.useCallback(
    (identifier: AssetFamilyIdentifier) =>
      redirect('akeneo_asset_manager_asset_family_edit', {
        identifier,
        tab: 'attribute',
      }),
    []
  );
  return {redirectToAsset, redirectToAssetFamily};
};

export type AssetAttributeFetcher = {
  fetchAll: (assetFamilyIdentifier: AssetFamilyIdentifier) => Promise<NormalizedAttribute[]>;
};

export type LibraryDataProvider = {
  assetFetcher: AssetFetcher;
  channelFetcher: ChannelFetcher;
  assetFamilyFetcher: AssetFamilyFetcher;
  assetAttributeFetcher: AssetAttributeFetcher;
};

type LibraryProps = {
  dataProvider: LibraryDataProvider;
  initialContext: Context;
};

const Library = ({dataProvider, initialContext}: LibraryProps) => {
  const [currentAssetFamilyIdentifier, setCurrentAssetFamilyIdentifier] = useStoredState<AssetFamilyIdentifier | null>(
    'akeneo.asset_manager.grid.current_asset_family',
    null,
    newAssetFamily => {
      if (null !== newAssetFamily) {
        // We need to reload the filters from local storage after changing the current asset family
        loadFilterCollectionFromStorage(`akeneo.asset_manager.grid.filter_collection_${newAssetFamily}`);
        clearImageLoadingQueue();
      }
    }
  );
  const [filterCollection, setFilterCollection, loadFilterCollectionFromStorage] = useStoredState<Filter[]>(
    `akeneo.asset_manager.grid.filter_collection_${currentAssetFamilyIdentifier}`,
    []
  );
  const [excludedAssetCollection] = React.useState<AssetCode[]>([]);
  const [selection, setSelection] = React.useState<AssetCode[]>([]);
  const [searchValue, setSearchValue] = useStoredState<string>('akeneo.asset_manager.grid.search_value', '');
  const [searchResult, setSearchResult] = React.useState<SearchResult<ListAsset>>({
    items: [],
    matchesCount: 0,
    totalCount: 0,
  });
  const [context, setContext] = useStoredState<Context>('akeneo.asset_manager.grid.context', initialContext);
  const [isCreateAssetModalOpen, setCreateAssetModalOpen] = React.useState<boolean>(false);
  const [isUploadModalOpen, setUploadModalOpen] = React.useState<boolean>(false);
  const [isCreateAssetFamilyModalOpen, setCreateAssetFamilyModalOpen] = React.useState<boolean>(false);
  const [isDeleteAllAssetsModalOpen, setDeleteAllAssetsModalOpen] = React.useState<boolean>(false);
  const channels = useChannels(dataProvider.channelFetcher);
  const locales = getLocales(channels, context.channel);
  const {assetFamily: currentAssetFamily, rights} = useAssetFamily(dataProvider, currentAssetFamilyIdentifier);
  const currentAssetFamilyLabel =
    null === currentAssetFamily
      ? ''
      : getLabel(currentAssetFamily.labels, context.locale, currentAssetFamily.identifier);

  const updateResults = useFetchResult(createQuery)(
    true,
    dataProvider,
    currentAssetFamilyIdentifier,
    filterCollection,
    searchValue,
    excludedAssetCollection,
    context,
    setSearchResult
  );
  const filterViews = useFilterViews(currentAssetFamilyIdentifier, dataProvider);
  const notify = useNotify();
  const {redirectToAsset, redirectToAssetFamily} = useRoute();

  const familyBreadcrumbConfiguration =
    null === currentAssetFamilyIdentifier
      ? []
      : [
          {
            action: {
              type: 'redirect',
              route: 'akeneo_asset_manager_asset_family_edit',
              parameters: {
                identifier: currentAssetFamilyIdentifier,
                tab: 'property',
              },
            },
            label: currentAssetFamilyLabel,
          },
        ];

  const hasMediaLinkAsMainMedia =
    null !== currentAssetFamily && isMediaLinkAttribute(getAttributeAsMainMedia(currentAssetFamily));

  return (
    <Container>
      <Column>
        <ColumnTitle>{__('pim_asset_manager.asset_family.column.title')}</ColumnTitle>
        <AssetFamilySelector
          assetFamilyIdentifier={currentAssetFamilyIdentifier}
          locale={context.locale}
          dataProvider={dataProvider}
          onChange={setCurrentAssetFamilyIdentifier}
        />
        <FilterCollection
          filterCollection={filterCollection}
          context={context}
          onFilterCollectionChange={setFilterCollection}
          orderedFilterViews={null === filterViews ? [] : filterViews}
        />
      </Column>
      <Content>
        <Header>
          <HeaderView
            label={__(
              'pim_asset_manager.result_counter',
              {count: searchResult.matchesCount},
              searchResult.matchesCount
            )}
            image={null}
            primaryAction={() => (
              <Buttons>
                {null !== currentAssetFamilyIdentifier ? (
                  <>
                    <Button color="outline" onClick={() => redirectToAssetFamily(currentAssetFamilyIdentifier)}>
                      {__(`pim_asset_manager.asset_family.button.${rights.assetFamily.edit ? 'edit' : 'view'}`)}
                    </Button>
                    <MultipleButton
                      color="green"
                      items={[
                        ...(rights.asset.create
                          ? [
                              {
                                label: __('pim_asset_manager.asset.button.create'),
                                action: () => setCreateAssetModalOpen(true),
                              },
                            ]
                          : []),
                        ...(rights.asset.upload || hasMediaLinkAsMainMedia
                          ? [
                              {
                                label: __('pim_asset_manager.asset.upload.title'),
                                title: __(
                                  `pim_asset_manager.asset.upload.${
                                    hasMediaLinkAsMainMedia ? 'disabled_for_media_link' : 'title'
                                  }`
                                ),
                                isDisabled: hasMediaLinkAsMainMedia,
                                action: () => setUploadModalOpen(true),
                              },
                            ]
                          : []),
                        ...(rights.assetFamily.create
                          ? [
                              {
                                label: __('pim_asset_manager.asset_family.button.create'),
                                action: () => setCreateAssetFamilyModalOpen(true),
                              },
                            ]
                          : []),
                      ]}
                    >
                      {__('pim_common.create')}
                    </MultipleButton>
                  </>
                ) : (
                  <Button color="green" onClick={() => setCreateAssetFamilyModalOpen(true)}>
                    {__('pim_asset_manager.asset_family.button.create')}
                  </Button>
                )}
              </Buttons>
            )}
            context={context}
            secondaryActions={() => (
              <SecondaryActions
                onOpenDeleteAllAssetsModal={() => setDeleteAllAssetsModalOpen(true)}
                canDeleteAllAssets={rights.asset.deleteAll}
              />
            )}
            withLocaleSwitcher={true}
            withChannelSwitcher={true}
            isDirty={false}
            isLoading={false}
            breadcrumbConfiguration={[
              {
                action: {
                  type: 'redirect',
                  route: 'akeneo_asset_manager_asset_family_index',
                },
                label: __('pim_asset_manager.asset_family.breadcrumb'),
              },
              ...familyBreadcrumbConfiguration,
            ]}
            displayActions={true}
          />
        </Header>
        <Grid>
          {null === currentAssetFamilyIdentifier ? (
            <>
              <HelperSection>
                <AssetIllustration size={80} />
                <HelperSeparator />
                <HelperTitle>
                  👋 {__('pim_asset_manager.asset_family.helper.title')}
                  <HelperText>
                    {__('pim_asset_manager.asset_family.helper.no_asset_family.text')}
                    <br />
                    <Link href="https://help.akeneo.com/" target="_blank">
                      {__('pim_asset_manager.asset_family.helper.no_asset_family.link')}
                    </Link>
                  </HelperText>
                </HelperTitle>
              </HelperSection>
              <NoDataSection>
                <AssetIllustration size={256} />
                <NoDataTitle>{__('pim_asset_manager.asset_family.no_data.no_asset_family.title')}</NoDataTitle>
                <NoDataText>
                  <Link onClick={() => setCreateAssetFamilyModalOpen(true)}>
                    {__('pim_asset_manager.asset_family.no_data.no_asset_family.link')}
                  </Link>
                </NoDataText>
              </NoDataSection>
            </>
          ) : 0 === searchResult.totalCount ? (
            <>
              <HelperSection>
                <AssetIllustration size={80} />
                <HelperSeparator />
                <HelperTitle>
                  👋 {__('pim_asset_manager.asset_family.helper.title')}
                  <HelperText>
                    {__('pim_asset_manager.asset_family.helper.no_asset.text', {family: currentAssetFamilyLabel})}
                  </HelperText>
                </HelperTitle>
              </HelperSection>
              <NoDataSection>
                <AssetIllustration size={256} />
                <NoDataTitle>{__('pim_asset_manager.asset_family.no_data.no_asset.title')}</NoDataTitle>
                <NoDataText>
                  <Link onClick={() => setCreateAssetModalOpen(true)}>
                    {__('pim_asset_manager.asset_family.no_data.no_asset.link')}
                  </Link>
                </NoDataText>
              </NoDataSection>
            </>
          ) : (
            <>
              <SearchBar
                dataProvider={dataProvider}
                searchValue={searchValue}
                context={context}
                resultCount={searchResult.matchesCount}
                onSearchChange={setSearchValue}
                onContextChange={setContext}
              />
              <Mosaic
                selection={selection}
                assetCollection={searchResult.items}
                context={context}
                resultCount={searchResult.matchesCount}
                hasReachMaximumSelection={false}
                onSelectionChange={setSelection}
                onAssetClick={(assetCode: AssetCode) => {
                  if (null !== currentAssetFamilyIdentifier) {
                    redirectToAsset(currentAssetFamilyIdentifier, assetCode);
                  }
                }}
              />
            </>
          )}
        </Grid>
      </Content>
      {isCreateAssetModalOpen && null !== currentAssetFamily && (
        <CreateModal
          locale={context.locale}
          assetFamily={currentAssetFamily}
          onClose={() => setCreateAssetModalOpen(false)}
          onAssetCreated={(assetCode: AssetCode, createAnother: boolean) => {
            notify('success', 'pim_asset_manager.asset.notification.create.success');
            if (createAnother) {
              updateResults();
            } else {
              redirectToAsset(currentAssetFamily.identifier, assetCode);
            }
          }}
        />
      )}
      {isUploadModalOpen && null !== currentAssetFamily && (
        <UploadModal
          locale={context.locale}
          channels={channels}
          locales={locales}
          assetFamily={currentAssetFamily}
          onCancel={() => {
            setUploadModalOpen(false);
            updateResults();
          }}
          onAssetCreated={() => {
            setUploadModalOpen(false);
            updateResults();
          }}
        />
      )}
      {isCreateAssetFamilyModalOpen && (
        <CreateAssetFamilyModal
          locale={context.locale}
          onClose={() => setCreateAssetFamilyModalOpen(false)}
          onAssetFamilyCreated={(assetFamilyIdentifier: AssetFamilyIdentifier) => {
            notify('success', 'pim_asset_manager.asset_family.notification.create.success');
            setCurrentAssetFamilyIdentifier(assetFamilyIdentifier);
            redirectToAssetFamily(assetFamilyIdentifier);
          }}
        />
      )}
      {isDeleteAllAssetsModalOpen && null !== currentAssetFamily && (
        <DeleteModal
          message={__('pim_asset_manager.asset.delete_all.confirm', {
            entityIdentifier: currentAssetFamilyIdentifier,
          })}
          title={__('pim_asset_manager.asset.delete.title')}
          onConfirm={() =>
            deleteAllAssetFamilyAssets(
              currentAssetFamily,
              () => {
                notify('success', 'pim_asset_manager.asset.notification.delete_all.success', {
                  entityIdentifier: currentAssetFamilyIdentifier,
                });
                setDeleteAllAssetsModalOpen(false);
                updateResults();
              },
              () => notify('error', 'pim_asset_manager.asset.notification.delete.fail')
            )
          }
          onCancel={() => setDeleteAllAssetsModalOpen(false)}
        />
      )}
    </Container>
  );
};

export default Library;
