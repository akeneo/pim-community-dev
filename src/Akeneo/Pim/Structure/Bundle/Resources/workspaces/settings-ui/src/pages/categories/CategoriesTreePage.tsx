import React, {FC, useEffect, useState} from 'react';
import {useParams} from 'react-router';
import {Breadcrumb, useBooleanState} from 'akeneo-design-system';
import {
  FullScreenError,
  PageContent,
  PageHeader,
  PimView,
  useRouter,
  useSecurity,
  useSetPageTitle,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {useCategoryTree} from '../../hooks';
import {CategoryTree} from '../../components';
import {NewCategoryModal} from './NewCategoryModal';

type Params = {
  treeId: string;
};

type NewCategoryState = {
  parentCode: string;
  onCreate: () => void;
};

const CategoriesTreePage: FC = () => {
  let {treeId} = useParams<Params>();
  const router = useRouter();
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const {tree, status, load} = useCategoryTree(parseInt(treeId));
  const [treeLabel, setTreeLabel] = useState(`[${treeId}]`);
  const [isNewCategoryModalOpen, openNewCategoryModal, closeNewCategoryModal] = useBooleanState();
  const [newCategory, setNewCategory] = useState<NewCategoryState | null>(null);

  useSetPageTitle(translate('pim_title.pim_enrich_categorytree_tree', {'category.label': treeLabel}));

  const followSettingsIndex = () => router.redirect(router.generate('pim_enrich_attribute_index'));
  const followCategoriesIndex = () => router.redirect(router.generate('pim_enrich_categorytree_index'));
  const followEditCategory = (id: number) => {
    if (!isGranted('pim_enrich_product_category_edit')) {
      return;
    }
    router.redirect(router.generate('pim_enrich_categorytree_edit', {id: id.toString()}));
  };

  const addCategory = (parentCode: string, onCreate: () => void) => {
    setNewCategory({parentCode, onCreate});
    openNewCategoryModal();
  };

  const handleCloseNewCategoryModal = () => {
    setNewCategory(null);
    closeNewCategoryModal();
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
          addCategory={isGranted('pim_enrich_product_category_create') ? addCategory : undefined}
          deleteCategory={categoryId => console.log(`delete category ${categoryId}`)} // @todo implement the deletion of the category and handle isGranted pim_enrich_product_category_remove
          // @todo define onCategoryMoved to save the move in database and request the 'pim_enrich_categorytree_movenode'
        />
        {isNewCategoryModalOpen && newCategory !== null && (
          <NewCategoryModal
            closeModal={handleCloseNewCategoryModal}
            onCreate={newCategory.onCreate}
            parentCode={newCategory.parentCode}
          />
        )}
      </PageContent>
    </>
  );
};
export {CategoriesTreePage};
