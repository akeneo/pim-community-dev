import React, {FC, useEffect} from 'react';
import {Breadcrumb, Button, useBooleanState} from 'akeneo-design-system';
import {PimView, useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {CategoryTreesDataGrid, EmptyCategoryTreeList} from '../../components';
import {useCategoryTreeList} from '../../hooks';
import {NewCategoryModal} from './NewCategoryModal';

const CategoriesIndex: FC = () => {
  const router = useRouter();
  const translate = useTranslate();
  const {trees, status, load} = useCategoryTreeList();
  const [isModalOpen, openModal, closeModal] = useBooleanState();
  const {isGranted} = useSecurity();

  const followSettingsIndex = () => router.redirect(router.generate('pim_enrich_attribute_index'));

  useEffect(() => {
    load();
  }, []);

  return (
    <>
      <PageHeader showPlaceholder={status === 'idle' || status === 'fetching'}>
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
              {translate('pim_common.create')}
            </Button>
          </PageHeader.Actions>
        )}
        <PageHeader.Title>
          {translate('pim_enrich.entity.category.page_title.index', {count: trees.length}, trees.length)}
        </PageHeader.Title>
      </PageHeader>
      <PageContent>
        {trees.length === 0 ? <EmptyCategoryTreeList /> : <CategoryTreesDataGrid trees={trees} />}
      </PageContent>
      {isModalOpen && <NewCategoryModal closeModal={closeModal} refreshCategoryTrees={load} />}
    </>
  );
};

export {CategoriesIndex};
