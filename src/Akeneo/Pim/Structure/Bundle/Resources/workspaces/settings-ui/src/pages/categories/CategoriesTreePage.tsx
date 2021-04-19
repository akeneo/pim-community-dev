import React, {FC, useCallback, useEffect, useState} from 'react';
import {useParams} from 'react-router';
import {Breadcrumb, Button, Tree} from 'akeneo-design-system';
import {PimView, useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {CategoryTree, FullScreenError, PageContent, PageHeader, useSetPageTitle} from '@akeneo-pim-community/shared';
import {useCategoryTree} from '../../hooks';

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

  const buildActions = useCallback((category: any, isRoot: boolean) => {
    const actions = [];

    if (isGranted('pim_enrich_product_category_create')) {
      actions.push(
        <Button ghost level={'primary'} size="small" onClick={() => alert(`create child for ${category.code}`)}>
          New Category
        </Button>
      );
    }

    if (!isRoot && isGranted('pim_enrich_product_category_remove')) {
      actions.push(
        <Button ghost level={'danger'} size="small" onClick={() => alert(`delete ${category.code}`)}>
          Delete
        </Button>
      );
    }

    return actions;
  }, [isGranted]);

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

        {tree === null ? (
          <Tree value="" label={treeLabel} isLoading={true}/>
        ) : (
          <>
            {/* @todo Adapt loading of the structure and model */}
            <CategoryTree
              onChange={() => console.log('change')}
              childrenCallback={() => Promise.resolve([])}
              init={() => Promise.resolve(tree)}
              style="list"
              onClick={
                isGranted('pim_enrich_product_category_edit') ? category => followEditCategory(category.id) : undefined
              }
              actions={buildActions}
            />
          </>
        )}
      </PageContent>
    </>
  );
};
export {CategoriesTreePage};
