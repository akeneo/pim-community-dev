import React, {FC, useEffect, useState} from 'react';
import {useParams} from 'react-router';
import {Breadcrumb, useBooleanState} from 'akeneo-design-system';
import {
  FullScreenError,
  NotificationLevel,
  PageContent,
  PageHeader,
  PimView,
  useNotify,
  useRouter,
  useSecurity,
  useSetPageTitle,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {useCategoryTree} from '../../hooks';
import {CategoryTree} from '../../components';
import {NewCategoryModal} from './NewCategoryModal';
import {DeleteCategoryModal} from '../../components/datagrids/categories/DeleteCategoryModal';
import {deleteCategory} from '../../infrastructure/removers';

type Params = {
  treeId: string;
};

type CategoryToCreate = {
  parentCode: string;
  onCreate: () => void;
};

type CategoryToDelete = {
  identifier: number;
  label: string;
  onDelete: () => void;
};

const MAX_NUMBER_OF_PRODUCTS_TO_ALLOW_DELETE = 100;

const CategoriesTreePage: FC = () => {
  let {treeId} = useParams<Params>();
  const router = useRouter();
  const translate = useTranslate();
  const notify = useNotify();
  const {isGranted} = useSecurity();
  const {tree, status, load} = useCategoryTree(parseInt(treeId));
  const [treeLabel, setTreeLabel] = useState(`[${treeId}]`);
  const [isNewCategoryModalOpen, openNewCategoryModal, closeNewCategoryModal] = useBooleanState();
  const [categoryToCreate, setCategoryToCreate] = useState<CategoryToCreate | null>(null);
  const [isDeleteCategoryModalOpen, openDeleteCategoryModal, closeDeleteCategoryModal] = useBooleanState();
  const [categoryToDelete, setCategoryToDelete] = useState<CategoryToDelete | null>(null);

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
    setCategoryToCreate({parentCode, onCreate});
    openNewCategoryModal();
  };

  const handleCloseNewCategoryModal = () => {
    setCategoryToCreate(null);
    closeNewCategoryModal();
  };

  const confirmDeleteCategory = async (identifier: number, label: string, numberOfProducts: number, onDelete: () => void) => {
    if (numberOfProducts > MAX_NUMBER_OF_PRODUCTS_TO_ALLOW_DELETE) {
      notify(
        NotificationLevel.INFO,
        translate('pim_enrich.entity.category.category_deletion.products_limit_exceeded.title'),
        translate('pim_enrich.entity.category.category_deletion.products_limit_exceeded.message', {
          name: label,
          limit: MAX_NUMBER_OF_PRODUCTS_TO_ALLOW_DELETE,
        })
      );

      return;
    }

    setCategoryToDelete({identifier, label, onDelete});
    openDeleteCategoryModal();
  };

  const handleCloseDeleteCategoryModal = () => {
    setCategoryToDelete(null);
    closeDeleteCategoryModal();
  };

  const handleDeleteCategory = async () => {
    if (categoryToDelete === null) {
      return;
    }

    const success = await deleteCategory(categoryToDelete.identifier);
    success && categoryToDelete.onDelete();

    const message = success
      ? 'pim_enrich.entity.category.category_deletion.success'
      : 'pim_enrich.entity.category.category_deletion.error';

    notify(
      success ? NotificationLevel.SUCCESS : NotificationLevel.ERROR,
      translate(message, {name: categoryToDelete.label})
    );

    handleCloseDeleteCategoryModal();
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
          deleteCategory={isGranted('pim_enrich_product_category_remove') ? confirmDeleteCategory : undefined}
          // @todo define onCategoryMoved to save the move in database and request the 'pim_enrich_categorytree_movenode'
        />
        {isNewCategoryModalOpen && categoryToCreate !== null && (
          <NewCategoryModal
            closeModal={handleCloseNewCategoryModal}
            onCreate={categoryToCreate.onCreate}
            parentCode={categoryToCreate.parentCode}
          />
        )}
        {isDeleteCategoryModalOpen && categoryToDelete !== null && (
          <DeleteCategoryModal
            categoryLabel={categoryToDelete.label}
            closeModal={handleCloseDeleteCategoryModal}
            deleteCategory={handleDeleteCategory}
            message={'pim_enrich.entity.category.category_deletion.confirmation'}
          />
        )}
      </PageContent>
    </>
  );
};
export {CategoriesTreePage};
