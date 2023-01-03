import React, {FC, useEffect} from 'react';
import {Breadcrumb, Button, useBooleanState} from 'akeneo-design-system';
import {PageContent, PageHeader, PimView, useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/shared';
import {CategoryTreesDataGrid, DiscoverEnrichedCategoriesInformationHelper, EmptyCategoryTreeList} from '../components';
import {useCategoryTreeList} from '../hooks';
import {NewCategoryModal} from './NewCategoryModal';

const CategoriesIndex: FC = () => {
  const router = useRouter();
  const translate = useTranslate();
  const {trees, loadingStatus, loadTrees} = useCategoryTreeList();
  const [isModalOpen, openModal, closeModal] = useBooleanState();
  const {isGranted} = useSecurity();

  const followSettingsIndex = () => router.redirect(router.generate('pim_settings_index'));

  useEffect(() => {
    loadTrees();
    sessionStorage.removeItem('lastSelectedCategory');
  }, [loadTrees]);

  return (
    <>
      <PageHeader showPlaceholder={loadingStatus === 'idle' || loadingStatus === 'fetching'}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={followSettingsIndex}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_enrich.entity.category.plural_label')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        {isGranted('pim_enrich_product_category_create') && (
          <PageHeader.Actions>
            <Button onClick={openModal} level="primary">
              {translate('akeneo.category.tree.create')}
            </Button>
          </PageHeader.Actions>
        )}
        <PageHeader.Title>
          {translate('pim_enrich.entity.category.page_title.index', {count: trees.length}, trees.length)}
        </PageHeader.Title>
      </PageHeader>
      <PageContent>
        {trees.length === 0 ? (
          <EmptyCategoryTreeList />
        ) : (
          <>
            <DiscoverEnrichedCategoriesInformationHelper />
            <CategoryTreesDataGrid trees={trees} refreshCategoryTrees={loadTrees} />
          </>
        )}
      </PageContent>
      {isModalOpen && <NewCategoryModal closeModal={closeModal} onCreate={loadTrees} />}
    </>
  );
};

export {CategoriesIndex};
