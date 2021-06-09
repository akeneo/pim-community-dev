import React, {FC, useEffect, useState} from 'react';
import {useParams} from 'react-router';
import {Breadcrumb, SectionTitle, useBooleanState} from 'akeneo-design-system';
import {
  BreadcrumbStepSkeleton,
  FullScreenError,
  PageContent,
  PageHeader,
  PimView,
  useRouter,
  useSecurity,
  useSetPageTitle,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {CategoryToDelete, useCategoryTree, useDeleteCategory} from '../../hooks';
import {CategoryTree} from '../../components';
import {NewCategoryModal} from './NewCategoryModal';
import {DeleteCategoryModal} from '../../components/datagrids/categories/DeleteCategoryModal';

type Params = {
  treeId: string;
};

type CategoryToCreate = {
  parentCode: string;
  onCreate: () => void;
};

const CategoriesTreePage: FC = () => {
  let {treeId} = useParams<Params>();
  const router = useRouter();
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const {tree, status, load} = useCategoryTree(parseInt(treeId));
  const [treeLabel, setTreeLabel] = useState<string>('');
  const [isNewCategoryModalOpen, openNewCategoryModal, closeNewCategoryModal] = useBooleanState();
  const [categoryToCreate, setCategoryToCreate] = useState<CategoryToCreate | null>(null);
  const [isDeleteCategoryModalOpen, openDeleteCategoryModal, closeDeleteCategoryModal] = useBooleanState();
  const [categoryToDelete, setCategoryToDelete] = useState<CategoryToDelete | null>(null);
  const {isCategoryDeletionPossible, handleDeleteCategory} = useDeleteCategory();

  useSetPageTitle(translate('pim_title.pim_enrich_categorytree_tree', {'category.label': treeLabel}));

  const followSettingsIndex = () => router.redirect(router.generate('pim_settings_index'));
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

  const confirmDeleteCategory = async (
    identifier: number,
    label: string,
    numberOfProducts: number,
    onDelete: () => void
  ) => {
    if (isCategoryDeletionPossible(label, numberOfProducts)) {
      setCategoryToDelete({identifier, label, onDelete});
      openDeleteCategoryModal();
    }
  };

  const handleCloseDeleteCategoryModal = () => {
    setCategoryToDelete(null);
    closeDeleteCategoryModal();
  };

  useEffect(() => {
    load();
  }, [treeId]);

  useEffect(() => {
    setTreeLabel(tree ? tree.label : '');
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
            <Breadcrumb.Step>{treeLabel || <BreadcrumbStepSkeleton />}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>{tree ? tree.label : ''}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <section>
          <SectionTitle>
            <SectionTitle.Title>{translate('pim_enrich.entity.category.plural_label')}</SectionTitle.Title>
          </SectionTitle>
          <CategoryTree
            root={tree}
            orderable={isGranted('pim_enrich_product_category_edit')}
            followCategory={
              isGranted('pim_enrich_product_category_edit') ? cat => followEditCategory(cat.id) : undefined
            }
            addCategory={isGranted('pim_enrich_product_category_create') ? addCategory : undefined}
            deleteCategory={isGranted('pim_enrich_product_category_remove') ? confirmDeleteCategory : undefined}
          />
        </section>
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
            deleteCategory={async () => {
              await handleDeleteCategory(categoryToDelete);
              handleCloseDeleteCategoryModal();
            }}
            message={'pim_enrich.entity.category.category_deletion.confirmation'}
          />
        )}
      </PageContent>
    </>
  );
};
export {CategoriesTreePage};
