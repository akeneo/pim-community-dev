import React, {FC, useEffect, useState} from 'react';
import {useParams} from 'react-router';
import {Breadcrumb} from 'akeneo-design-system';
import {PimView} from '@akeneo-pim-community/legacy-bridge';
import {
  FullScreenError,
  PageContent,
  PageHeader,
  useRouter,
  useSecurity,
  useSetPageTitle,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {useCategoryTree} from '../../hooks';
import {CategoryTree} from '../../components';

type Params = {
  treeId: string;
};

const CategoriesTreePage: FC = () => {
  let {treeId} = useParams<Params>();
  const router = useRouter();
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const {tree, status, load} = useCategoryTree(parseInt(treeId));
  const [treeLabel, setTreeLabel] = useState(`[${treeId}]`);

  useSetPageTitle(translate('pim_title.pim_enrich_categorytree_tree', {'category.label': treeLabel}));

  const followSettingsIndex = () => router.redirect(router.generate('pim_enrich_attribute_index'));
  const followCategoriesIndex = () => router.redirect(router.generate('pim_enrich_categorytree_index'));
  const followEditCategory = (id: number) => {
    if (!isGranted('pim_enrich_product_category_edit')) {
      return;
    }
    router.redirect(router.generate('pim_enrich_categorytree_edit', {id: id.toString()}));
  };

  useEffect(() => {
    load();
  }, [treeId]);

  useEffect(() => {
    setTreeLabel(tree ? tree.label : `[${treeId}]`);
  }, [tree]);

  if (status === 'error') {
    return (
      <FullScreenError
        title={translate('error.exception', {status_code: '404'})}
        message={translate('pim_enrich.entity.category.content.tree.not_found')}
        code={404}
      />
    );
  }

  return (
    <>
      <PageHeader showPlaceholder={status === 'idle' || status === 'fetching'}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={followSettingsIndex}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step onClick={followCategoriesIndex}>
              {translate('pim_enrich.entity.category.plural_label')}
            </Breadcrumb.Step>
            <Breadcrumb.Step>{treeLabel}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>{treeLabel}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <CategoryTree
          root={tree}
          rootLabel={treeLabel}
          sortable={isGranted('pim_enrich_product_category_edit')}
          followCategory={isGranted('pim_enrich_product_category_edit') ? cat => followEditCategory(cat.id) : undefined}
          addCategory={categoryId => console.log(`add new category in ${categoryId}`)} // @todo implement the creation of a new category and handle isGranted pim_enrich_product_category_create
          deleteCategory={categoryId => console.log(`delete category ${categoryId}`)} // @todo implement the deletion of the category and handle isGranted pim_enrich_product_category_remove
          // @todo define onCategoryMoved to save the move in database and request the 'pim_enrich_categorytree_movenode'
        />
      </PageContent>
    </>
  );
};
export {CategoriesTreePage};
