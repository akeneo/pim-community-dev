import React, {useState, useEffect} from 'react';
import {NotificationLevel, PimView, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {PageContent, PageHeader, SearchBar, useDebounceCallback} from '@akeneo-pim-community/shared';
import {Breadcrumb, getFontSize, Pagination} from 'akeneo-design-system';
import styled from 'styled-components';
import {removeAssociationType} from '@akeneo-pim-community/settings-ui/src/infrastructure/removers/associationTypeRemover';
import {
  AssociationTypesDataGrid,
  useAssociationTypes,
  NoAssociationTypes,
  AssociationType,
  DeleteConfirmation,
} from '@akeneo-pim-community/settings-ui';

const DatagridState = require('pim/datagrid/state');

const AssociationTypesSearchBar = styled(SearchBar)`
  margin: 10px 0 20px;
`;

const CreatePimView = styled(PimView)`
  .AknButton {
    padding: 0 15px;
    font-size: ${getFontSize('default')};
  }
`;

export type DeleteAssociationTypeRequest = {
  showConfirm: boolean;
  associationType: AssociationType | null;
};

const AssociationTypesIndex = () => {
  const translate = useTranslate();
  const notify = useNotify();
  const settingsHomeRoute = useRoute('pim_enrich_attribute_rest_index');

  const {associationTypes, countAssociationTypes, search} = useAssociationTypes();

  const [searchString, setSearchString] = useState<string>('');
  const [sortDirection, setSortDirection] = useState<string>('ASC');
  const [page, setPage] = useState<number>(1);
  const [deleteAssociationTypeRequest, setDeleteAssociationTypeRequest] = useState<DeleteAssociationTypeRequest>({
    showConfirm: false,
    associationType: null,
  });

  const debouncedSearch = useDebounceCallback(search, 300);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    setPage(1);
    debouncedSearch(searchValue, sortDirection, 1);
  };

  const onDirectionChange = (direction: string) => {
    const newSortDirection = direction === 'descending' ? 'DESC' : 'ASC';
    setSortDirection(newSortDirection);
    search(searchString, newSortDirection, page);
  };

  const followPage = (newPage: any) => {
    if (newPage !== page) {
      setPage(newPage);
      search(searchString, sortDirection, newPage);
    }
  };

  const deleteAssociationType = async (
    associationType: AssociationType | null,
    setDeleteAssociationTypeRequest: any
  ) => {
    if (associationType === null) {
      return;
    }

    const deleteAssociationTypeSuccess = await removeAssociationType(associationType);
    setDeleteAssociationTypeRequest({showConfirm: false, associationType: null});

    if (deleteAssociationTypeSuccess) {
      const searchPage = associationTypes.length === 1 && page > 1 ? 1 : page;
      search(searchString, sortDirection, searchPage);
      notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.association_type.flash.delete.success'));
    } else {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.association_type.flash.delete.fail'));
    }
  };

  useEffect(() => {
    const defaultSortDirection = DatagridState.get('association-type-grid', 'sortDirection') || 'ASC';
    setSortDirection(defaultSortDirection);

    const defaultSearchString = DatagridState.get('association-type-grid', 'searchString') || '';
    setSearchString(defaultSearchString);

    const defaultPage = parseInt(DatagridState.get('association-type-grid', 'page') || '1');
    setPage(defaultPage);

    search(defaultSearchString, defaultSortDirection, defaultPage);
  }, []);

  useEffect(() => {
    DatagridState.set('association-type-grid', {searchString, sortDirection, page});
  }, [searchString, sortDirection, page]);

  console.log('render');

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${settingsHomeRoute}`}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.association_type')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <CreatePimView viewName="pim-association-type-index-create-button" />
        </PageHeader.Actions>
        <PageHeader.Title>
          {translate(
            'pim_enrich.entity.association_type.page_title.index',
            {count: countAssociationTypes},
            countAssociationTypes
          )}
        </PageHeader.Title>
      </PageHeader>
      <PageContent>
        {countAssociationTypes === 0 && searchString === '' ? (
          <NoAssociationTypes />
        ) : (
          <>
            <AssociationTypesSearchBar
              count={countAssociationTypes}
              searchValue={searchString}
              placeholder={translate('pim_common.search')}
              onSearchChange={onSearch}
            />
            <Pagination
              currentPage={countAssociationTypes > 0 ? page : 0}
              totalItems={countAssociationTypes}
              followPage={followPage}
            />
            <AssociationTypesDataGrid
              associationTypes={associationTypes}
              searchString={searchString}
              sortDirection={sortDirection}
              onDirectionChange={onDirectionChange}
              deleteAssociationType={(associationType: AssociationType) => {
                setDeleteAssociationTypeRequest({showConfirm: true, associationType});
              }}
            />
            {deleteAssociationTypeRequest.showConfirm && (
              <DeleteConfirmation
                deleteAction={() =>
                  deleteAssociationType(deleteAssociationTypeRequest.associationType, setDeleteAssociationTypeRequest)
                }
                cancelDelete={() => setDeleteAssociationTypeRequest({showConfirm: false, associationType: null})}
              />
            )}
          </>
        )}
      </PageContent>
    </>
  );
};

export {AssociationTypesIndex};
