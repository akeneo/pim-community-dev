import React, {FC, useEffect, useState} from 'react';
import {useParams} from 'react-router';
import {Breadcrumb, SectionTitle, SkeletonPlaceholder, useBooleanState} from 'akeneo-design-system';
import {
  FullScreenError,
  PageContent,
  PageHeader,
  PimView,
  useRouter,
  useSecurity,
  useSessionStorageState,
  useSetPageTitle,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {CategoryToDelete, useCategoryTreeDeprecated, useDeleteCategory} from '../hooks';
import {CategoryTree, DiscoverEnrichedCategoriesInformationHelper} from '../components';
import {NewCategoryModal} from './NewCategoryModal';
import {DeleteCategoryModal} from '../components/datagrids/DeleteCategoryModal';

type Params = {
  treeId: string;
};

type CategoryToCreate = {
  parentCode: string;
  onCreate: () => void;
};

type lastSelectedCategory = {
  treeId: string;
  categoryId: string;
};

const CategoriesTreePage: FC = () => {
  let {treeId} = useParams<Params>();
  const router = useRouter();
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const [lastSelectedCategory] = useSessionStorageState<lastSelectedCategory>(
    {
      treeId: treeId,
      categoryId: '-1',
    },
    'lastSelectedCategory'
  );
  const {tree, loadingStatus, loadTree} = useCategoryTreeDeprecated(
    parseInt(treeId),
    lastSelectedCategory.treeId === treeId ? lastSelectedCategory.categoryId : '-1'
  );
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
    code: string,
    numberOfProducts: number,
    onDelete: () => void
  ) => {
    if (isCategoryDeletionPossible(label, numberOfProducts)) {
      setCategoryToDelete({identifier, label, code, numberOfProducts, onDelete});
      openDeleteCategoryModal();
    }
  };

  const handleCloseDeleteCategoryModal = () => {
    setCategoryToDelete(null);
    closeDeleteCategoryModal();
  };

  useEffect(() => {
    loadTree();
  }, [loadTree, treeId]);

  useEffect(() => {
    setTreeLabel(tree ? tree.label : '');
  }, [tree]);

  if (loadingStatus === 'error') {
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
      <PageHeader showPlaceholder={loadingStatus === 'idle' || loadingStatus === 'fetching'}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={followSettingsIndex}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step onClick={followCategoriesIndex}>
              {translate('pim_enrich.entity.category.plural_label')}
            </Breadcrumb.Step>
            <Breadcrumb.Step>
              {treeLabel || <SkeletonPlaceholder as="span">{treeId}</SkeletonPlaceholder>}
            </Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>{tree?.label ?? treeId}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <section>
          <DiscoverEnrichedCategoriesInformationHelper />
          <SectionTitle>
            <SectionTitle.Title>{translate('pim_enrich.entity.category.plural_label')}</SectionTitle.Title>
          </SectionTitle>
          <CategoryTree
            root={tree}
            orderable={isGranted('pim_enrich_product_category_order_trees')}
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
            message={'pim_enrich.entity.category.category_deletion.confirmation_question'}
            categoryId={categoryToDelete.identifier}
            numberOfProducts={categoryToDelete.numberOfProducts}
          />
        )}
      </PageContent>
    </>
  );
};
export {CategoriesTreePage};
