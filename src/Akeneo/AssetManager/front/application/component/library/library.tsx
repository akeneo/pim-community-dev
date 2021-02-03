import React, {useEffect, useCallback, useState} from 'react';
import styled from 'styled-components';
import SearchBar from 'akeneoassetmanager/application/component/asset/list/search-bar';
import Mosaic from 'akeneoassetmanager/application/component/asset/list/mosaic';
import {Context} from 'akeneoassetmanager/domain/model/context';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {SearchResult, emptySearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import {useFetchResult, createQuery} from 'akeneoassetmanager/application/hooks/grid';
import FilterCollection, {useFilterViews} from 'akeneoassetmanager/application/component/asset/list/filter-collection';
import {AssetFamilySelector} from 'akeneoassetmanager/application/component/library/AssetFamilySelector';
import {HeaderView} from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {getLabel} from 'pimui/js/i18n';
import {MultipleButton, ButtonContainer} from 'akeneoassetmanager/application/component/app/button';
import UploadModal from 'akeneoassetmanager/application/asset-upload/component/modal';
import {useAssetFamily} from 'akeneoassetmanager/application/hooks/asset-family';
import {CreateModal} from 'akeneoassetmanager/application/component/asset/create';
import {CreateAssetFamilyModal} from 'akeneoassetmanager/application/component/asset-family/create';
import {useStoredState} from 'akeneoassetmanager/application/hooks/state';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import {useChannels} from 'akeneoassetmanager/application/hooks/channel';
import {Column} from 'akeneoassetmanager/application/component/app/column';
import AssetFetcher from 'akeneoassetmanager/domain/fetcher/asset';
import {ChannelFetcher} from 'akeneoassetmanager/application/hooks/channel';
import {AssetFamilyFetcher} from 'akeneoassetmanager/domain/fetcher/asset-family';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {clearImageLoadingQueue} from 'akeneoassetmanager/tools/image-loader';
import {getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {useScroll} from 'akeneoassetmanager/application/hooks/scroll';
import {CompletenessValue} from 'akeneoassetmanager/application/component/asset/list/completeness-filter';
import {getCompletenessFilter, updateCompletenessFilter} from 'akeneoassetmanager/tools/filters/completeness';
import {useNotify, NotificationLevel, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AssetFamilyBreadcrumb} from 'akeneoassetmanager/application/component/app/breadcrumb';
import {Checkbox, Toolbar, Button, useSelection, useBooleanState} from 'akeneo-design-system';
import {MassDelete} from 'akeneoassetmanager/application/component/library/MassDelete/MassDelete';
import {useSelectionQuery} from 'akeneoassetmanager/application/component/library/hooks/useSelectionQuery';
import {useRoutes} from 'akeneoassetmanager/application/component/library/hooks/useRoutes';
import {
  NoAssetFamily,
  NoAsset,
  AssetLibraryPlaceholder,
} from 'akeneoassetmanager/application/component/library/components';
import {MassEdit} from 'akeneoassetmanager/application/component/library/MassEdit/MassEdit';

const Header = styled.div`
  padding-left: 40px;
  padding-right: 40px;
  height: 136px;
`;

const Content = styled.div`
  flex: 1;
  display: flex;
  flex-direction: column;
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
  margin: 0 40px;
  overflow-y: auto;
`;

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
    null
  );
  const [scrollContainerRef, scrollTop] = useScroll<HTMLDivElement>();
  const [filterCollection, setFilterCollection] = useStoredState<Filter[]>(
    `akeneo.asset_manager.grid.filter_collection_${currentAssetFamilyIdentifier}`,
    []
  );
  const [excludedAssetCollection] = useState<AssetCode[]>([]);
  const [searchValue, setSearchValue] = useStoredState<string>('akeneo.asset_manager.grid.search_value', '');
  const [searchResult, setSearchResult] = useState<SearchResult<ListAsset>>(emptySearchResult());
  const [isInitialized, setIsInitialized] = useState<boolean>(false);
  const [context, setContext] = useStoredState<Context>('akeneo.asset_manager.grid.context', initialContext);
  const [isCreateAssetModalOpen, openCreateAssetModalOpen, closeCreateAssetModalOpen] = useBooleanState(false);
  const [isUploadModalOpen, openUploadModal, closeUploadModal] = useBooleanState(false);
  const [isCreateAssetFamilyModalOpen, openCreateAssetFamilyModal, closeCreateAssetFamilyModal] = useBooleanState(
    false
  );
  const notify = useNotify();
  const translate = useTranslate();

  const [
    selection,
    selectionState,
    isItemSelected,
    onSelectionChange,
    onSelectAllChange,
    selectedCount,
  ] = useSelection<AssetCode>(searchResult.matchesCount);

  const channels = useChannels(dataProvider.channelFetcher);
  const locales = getLocales(channels, context.channel);
  const {assetFamily: currentAssetFamily, rights} = useAssetFamily(dataProvider, currentAssetFamilyIdentifier);
  const currentAssetFamilyLabel =
    null === currentAssetFamily
      ? ''
      : getLabel(currentAssetFamily.labels, context.locale, currentAssetFamily.identifier);

  const completenessValue = getCompletenessFilter(filterCollection);
  const handleCompletenessValueChange = useCallback(
    (value: CompletenessValue) => {
      setFilterCollection(updateCompletenessFilter(filterCollection, value));
    },
    [filterCollection, setFilterCollection]
  );

  const selectionQuery = useSelectionQuery(
    currentAssetFamilyIdentifier,
    filterCollection,
    searchValue,
    context,
    selection
  );

  const updateResults = useFetchResult(createQuery)(
    true,
    dataProvider,
    currentAssetFamilyIdentifier,
    filterCollection,
    searchValue,
    excludedAssetCollection,
    context,
    (results: SearchResult<ListAsset>): void => {
      setSearchResult(results);
      setIsInitialized(true);
    }
  );
  const filterViews = useFilterViews(currentAssetFamilyIdentifier, dataProvider);
  const {redirectToAsset, redirectToAssetFamily} = useRoutes();

  const hasMediaLinkAsMainMedia =
    null !== currentAssetFamily && isMediaLinkAttribute(getAttributeAsMainMedia(currentAssetFamily));

  const handleAssetFamilyChange = useCallback(
    (assetFamilyIdentifier: AssetFamilyIdentifier) => {
      setCurrentAssetFamilyIdentifier(assetFamilyIdentifier);
      clearImageLoadingQueue();
    },
    [setCurrentAssetFamilyIdentifier]
  );

  const canSelectAssets = rights.asset.delete || rights.asset.edit;
  const isToolbarVisible = 0 < searchResult.matchesCount && !!selectionState && canSelectAssets;

  useEffect(() => {
    scrollTop();
    onSelectAllChange(false);
  }, [currentAssetFamilyIdentifier, filterCollection, searchValue, context]);

  return (
    <Container>
      <Column title={translate('pim_asset_manager.asset_family.column.title')}>
        <AssetFamilySelector
          assetFamilyIdentifier={currentAssetFamilyIdentifier}
          locale={context.locale}
          dataProvider={dataProvider}
          onChange={handleAssetFamilyChange}
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
            label={
              isInitialized
                ? translate(
                    'pim_asset_manager.result_counter',
                    {count: searchResult.matchesCount},
                    searchResult.matchesCount
                  )
                : ''
            }
            image={null}
            primaryAction={() => (
              <ButtonContainer>
                {null !== currentAssetFamilyIdentifier ? (
                  <>
                    <Button
                      ghost={true}
                      level="tertiary"
                      onClick={() => redirectToAssetFamily(currentAssetFamilyIdentifier)}
                    >
                      {translate(`pim_asset_manager.asset_family.button.${rights.assetFamily.edit ? 'edit' : 'view'}`)}
                    </Button>
                    <MultipleButton
                      items={[
                        ...(rights.asset.create
                          ? [
                              {
                                label: translate('pim_asset_manager.asset.button.create'),
                                action: openCreateAssetModalOpen,
                              },
                            ]
                          : []),
                        ...(rights.asset.upload || hasMediaLinkAsMainMedia
                          ? [
                              {
                                label: translate('pim_asset_manager.asset.upload.title'),
                                title: translate(
                                  `pim_asset_manager.asset.upload.${
                                    hasMediaLinkAsMainMedia ? 'disabled_for_media_link' : 'title'
                                  }`
                                ),
                                isDisabled: hasMediaLinkAsMainMedia,
                                action: openUploadModal,
                              },
                            ]
                          : []),
                        ...(rights.assetFamily.create
                          ? [
                              {
                                label: translate('pim_asset_manager.asset_family.button.create'),
                                action: openCreateAssetFamilyModal,
                              },
                            ]
                          : []),
                      ]}
                    >
                      {translate('pim_common.create')}
                    </MultipleButton>
                  </>
                ) : (
                  <Button level="primary" onClick={openCreateAssetFamilyModal}>
                    {translate('pim_asset_manager.asset_family.button.create')}
                  </Button>
                )}
              </ButtonContainer>
            )}
            context={context}
            withLocaleSwitcher={true}
            withChannelSwitcher={true}
            isDirty={false}
            isLoading={false}
            breadcrumb={<AssetFamilyBreadcrumb assetFamilyLabel={currentAssetFamilyLabel} />}
            displayActions={true}
          />
        </Header>
        <Grid>
          {!isInitialized ? (
            <AssetLibraryPlaceholder assetFamily={currentAssetFamily} />
          ) : null === currentAssetFamilyIdentifier ? (
            <NoAssetFamily onCreateAssetFamily={openCreateAssetFamilyModal} />
          ) : 0 === searchResult.totalCount ? (
            <NoAsset assetFamilyLabel={currentAssetFamilyLabel} onCreateAsset={openCreateAssetModalOpen} />
          ) : (
            <>
              <SearchBar
                dataProvider={dataProvider}
                searchValue={searchValue}
                context={context}
                resultCount={searchResult.matchesCount}
                onSearchChange={setSearchValue}
                onContextChange={setContext}
                completenessValue={completenessValue}
                onCompletenessChange={handleCompletenessValueChange}
              />
              <Mosaic
                scrollContainerRef={scrollContainerRef}
                assetCollection={searchResult.items}
                context={context}
                resultCount={searchResult.matchesCount}
                selectionState={selectionState}
                hasReachMaximumSelection={false}
                onSelectionChange={canSelectAssets ? onSelectionChange : undefined}
                isItemSelected={isItemSelected}
                onAssetClick={(assetCode: AssetCode) => {
                  if (null !== currentAssetFamilyIdentifier) {
                    redirectToAsset(currentAssetFamilyIdentifier, assetCode);
                  }
                }}
              />
            </>
          )}
        </Grid>
        <Toolbar isVisible={isToolbarVisible}>
          <Toolbar.SelectionContainer>
            <Checkbox checked={selectionState} onChange={onSelectAllChange} />
          </Toolbar.SelectionContainer>
          <Toolbar.LabelContainer>
            {translate('pim_asset_manager.asset_selected', {assetCount: selectedCount}, selectedCount)}
          </Toolbar.LabelContainer>
          <Toolbar.ActionsContainer>
            {rights.asset.edit && (
              <MassEdit
                selectionQuery={selectionQuery}
                onConfirm={() => {
                  onSelectAllChange(false);
                }}
                context={context}
                assetFamily={currentAssetFamily}
                selectedCount={selectedCount}
              />
            )}
            {rights.asset.delete && (
              <MassDelete
                selectionQuery={selectionQuery}
                onConfirm={() => {
                  onSelectAllChange(false);
                }}
                assetFamily={currentAssetFamily}
                selectedCount={selectedCount}
              />
            )}
          </Toolbar.ActionsContainer>
        </Toolbar>
      </Content>
      {isCreateAssetModalOpen && null !== currentAssetFamily && (
        <CreateModal
          locale={context.locale}
          assetFamily={currentAssetFamily}
          onClose={closeCreateAssetModalOpen}
          onAssetCreated={(assetCode: AssetCode, createAnother: boolean) => {
            notify(NotificationLevel.SUCCESS, translate('pim_asset_manager.asset.notification.create.success'));
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
          confirmLabel={translate('pim_asset_manager.asset.upload.confirm')}
          locale={context.locale}
          channels={channels}
          locales={locales}
          assetFamily={currentAssetFamily}
          onCancel={() => {
            closeUploadModal();
            updateResults();
          }}
          onAssetCreated={() => {
            closeUploadModal();
            updateResults();
          }}
        />
      )}
      {isCreateAssetFamilyModalOpen && (
        <CreateAssetFamilyModal
          locale={context.locale}
          onClose={closeCreateAssetFamilyModal}
          onAssetFamilyCreated={(assetFamilyIdentifier: AssetFamilyIdentifier) => {
            notify(NotificationLevel.SUCCESS, translate('pim_asset_manager.asset_family.notification.create.success'));
            handleAssetFamilyChange(assetFamilyIdentifier);
            redirectToAssetFamily(assetFamilyIdentifier);
          }}
        />
      )}
    </Container>
  );
};

export default Library;
