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
import {useFetchResult} from 'akeneoassetmanager/application/library/hooks/grid';
import FilterCollection, {useFilterViews} from 'akeneoassetmanager/application/component/asset/list/filter-collection';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoassetmanager/tools/translator';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import {AssetFamilySelector} from 'akeneoassetmanager/application/library/component/asset-family-selector';
import assetAttributeFetcher from 'akeneoassetmanager/infrastructure/fetcher/attribute';
import {HeaderView} from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {getLabel} from 'pimui/js/i18n';
import {MultipleButton, Button} from 'akeneoassetmanager/application/component/app/button';
import UploadModal from 'akeneoassetmanager/application/asset-upload/component/modal';
import {useAssetFamily} from 'akeneoassetmanager/application/library/hooks/asset-family';
import {CreateModal} from 'akeneoassetmanager/application/component/asset/create';
import {useNotify} from 'akeneoassetmanager/application/library/hooks/notify';
import {CreateAssetFamilyModal} from 'akeneoassetmanager/application/component/asset-family/create';
import {useRedirect} from 'akeneoassetmanager/application/library/hooks/router';
import {useAssetFamilyRights} from 'akeneoassetmanager/application/library/hooks/rights';
import {useStoredState} from 'akeneoassetmanager/application/library/hooks/state';

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

const Header = styled.div`
  padding-left: 40px;
  padding-right: 40px;
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

const Buttons = styled.div`
  display: flex;
  > :not(:first-child) {
    margin-left: 10px;
  }
`;

const dataProvider = {
  assetFetcher,
  channelFetcher: {
    fetchAll: fetchAllChannels,
  },
  assetFamilyFetcher,
  assetAttributesFetcher: {
    fetchAll: (assetFamilyIdentifier: AssetFamilyIdentifier) => assetAttributeFetcher.fetchAll(assetFamilyIdentifier),
  },
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

type LibraryProps = {
  initialContext: Context;
};

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

const Library = ({initialContext}: LibraryProps) => {
  const [currentAssetFamilyIdentifier, setCurrentAssetFamilyIdentifier] = useStoredState<AssetFamilyIdentifier | null>(
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
    `akeneo.asset_manager.grid.filter_collection_${currentAssetFamilyIdentifier}`,
    []
  );
  const [excludedAssetCollection] = React.useState<AssetCode[]>([]);
  const [selection, setSelection] = React.useState<AssetCode[]>([]);
  const [searchValue, setSearchValue] = useStoredState<string>('akeneo.asset_manager.grid.search_value', '');
  const [resultCount, setResultCount] = React.useState<number>(0);
  const [resultCollection, setResultCollection] = React.useState<ListAsset[]>([]);
  const [context, setContext] = useStoredState<Context>('akeneo.asset_manager.grid.context', initialContext);
  const [isCreateAssetModalOpen, setCreateAssetModalOpen] = React.useState<boolean>(false);
  const [isUploadModalOpen, setUploadModalOpen] = React.useState<boolean>(false);
  const [isCreateAssetFamilyModalOpen, setCreateAssetFamilyModalOpen] = React.useState<boolean>(false);
  const currentAssetFamily = useAssetFamily(dataProvider, currentAssetFamilyIdentifier);
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
    setResultCollection,
    setResultCount
  );
  const filterViews = useFilterViews(currentAssetFamilyIdentifier, dataProvider);
  const notify = useNotify();
  const rights = useAssetFamilyRights(currentAssetFamilyIdentifier);
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

  return (
    <Container>
      <Column>
        <ColumnTitle>{__('pim_asset_manager.asset_family.column_title')}</ColumnTitle>
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
            label={__('pim_asset_manager.result_counter', {count: resultCount}, resultCount)}
            image={null}
            primaryAction={() => (
              <Buttons>
                {null !== currentAssetFamilyIdentifier && (
                  <Button color="outline" onClick={() => redirectToAssetFamily(currentAssetFamilyIdentifier)}>
                    {__(`pim_asset_manager.asset_family.button.${rights.assetFamily.edit ? 'edit' : 'view'}`)}
                  </Button>
                )}
                {rights.asset.create && rights.asset.upload && rights.assetFamily.create && (
                  <MultipleButton
                    color="green"
                    items={[
                      {
                        label: __('pim_asset_manager.asset.button.create'),
                        action: () => setCreateAssetModalOpen(true),
                      },
                      {
                        label: __('pim_asset_manager.asset.upload.title'),
                        action: () => setUploadModalOpen(true),
                      },
                      {
                        label: __('pim_asset_manager.asset_family.button.create'),
                        action: () => setCreateAssetFamilyModalOpen(true),
                      },
                    ]}
                  />
                )}
              </Buttons>
            )}
            context={context}
            secondaryActions={() => (
              <SecondaryActions
                onOpenDeleteAllAssetsModal={() => {
                  //TODO events.onOpenDeleteAllAssetsModal();
                }}
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
              if (null !== currentAssetFamilyIdentifier) {
                redirectToAsset(currentAssetFamilyIdentifier, assetCode);
              }
            }}
          />
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
          assetFamily={currentAssetFamily}
          onCancel={() => setUploadModalOpen(false)}
          onAssetCreated={updateResults}
        />
      )}
      {isCreateAssetFamilyModalOpen && (
        <CreateAssetFamilyModal
          locale={context.locale}
          onClose={() => setCreateAssetFamilyModalOpen(false)}
          onAssetFamilyCreated={(assetFamilyIdentifier: AssetFamilyIdentifier) => {
            notify('success', 'pim_asset_manager.asset_family.notification.create.success');
            redirectToAssetFamily(assetFamilyIdentifier);
          }}
        />
      )}
    </Container>
  );
};

export default Library;
