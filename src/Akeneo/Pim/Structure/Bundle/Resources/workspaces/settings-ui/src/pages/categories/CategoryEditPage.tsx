import React, {FC} from 'react';
import {useParams} from 'react-router';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {Breadcrumb} from 'akeneo-design-system';
import {PimView, useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useSetPageTitle} from '../../hooks';

type Params = {
  categoryId: string;
};
const CategoryEditPage: FC = () => {
  const {categoryId} = useParams<Params>();
  const translate = useTranslate();
  const router = useRouter();
  const categoryLabel = `[${categoryId}]`;
  const treeLabel = `Tree`;

  useSetPageTitle(translate('pim_title.pim_enrich_categorytree_edit', {'category.label': categoryLabel}));

  const followSettingsIndex = () => router.redirect(router.generate('pim_enrich_attribute_index'));
  const followCategoriesIndex = () => router.redirect(router.generate('pim_enrich_categorytree_index'));
  const followCategoryTree = () => router.redirect(router.generate('pim_enrich_categorytree_tree', {id: 1}));

  return (
    <>
      <PageHeader showPlaceholder={status === 'idle' || status === 'fetching'}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={followSettingsIndex}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step onClick={followCategoriesIndex}>
              {translate('pim_enrich.entity.category.plural_label')}
            </Breadcrumb.Step>
            <Breadcrumb.Step onClick={followCategoryTree}>{treeLabel}</Breadcrumb.Step>
            <Breadcrumb.Step>{categoryLabel}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>{categoryLabel}</PageHeader.Title>
      </PageHeader>
      <PageContent>{categoryLabel}</PageContent>
    </>
  );
};
export {CategoryEditPage};
