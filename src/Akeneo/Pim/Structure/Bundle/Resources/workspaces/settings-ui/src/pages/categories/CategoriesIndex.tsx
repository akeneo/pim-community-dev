import React, {FC, useEffect} from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {PimView, useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {CategoryTreesDataGrid, EmptyCategoryTreeList} from '../../components';
import {useCategoryTreeList} from '../../hooks';

const CategoriesIndex: FC = () => {
  const router = useRouter();
  const translate = useTranslate();
  const {trees, isPending, load} = useCategoryTreeList();

  const followSettingsIndex = () => router.redirect(router.generate('pim_enrich_attribute_index'));

  useEffect(() => {
    load();
  }, []);

  return (
    <>
      <PageHeader showPlaceholder={isPending}>
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
        <PageHeader.Title>
          {translate('pim_enrich.entity.category.page_title.index', {count: trees.length}, trees.length)}
        </PageHeader.Title>
      </PageHeader>
      <PageContent>
        {trees.length === 0 ? <EmptyCategoryTreeList /> : <CategoryTreesDataGrid trees={trees} />}
      </PageContent>
    </>
  );
};

export {CategoriesIndex};
