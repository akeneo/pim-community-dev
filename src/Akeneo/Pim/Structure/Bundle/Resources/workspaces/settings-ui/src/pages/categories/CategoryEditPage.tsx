import React, {FC, useEffect, useState} from 'react';
import {useParams} from 'react-router';
import {
  Breadcrumb,
  Button,
  Dropdown,
  IconButton,
  MoreIcon,
  TabBar,
  useBooleanState,
  useTabBar,
} from 'akeneo-design-system';
import {
  BreadcrumbStepSkeleton,
  FullScreenError,
  PageContent,
  PageHeader,
  PimView,
  UnsavedChanges,
  useRouter,
  useSecurity,
  useSessionStorageState,
  useSetPageTitle,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {
  CategoryToDelete,
  useCountProductsBeforeDeleteCategory,
  useDeleteCategory,
  useEditCategoryForm,
} from '../../hooks';
import {Category} from '../../models';
import {HistoryPimView, View} from './HistoryPimView';
import {DeleteCategoryModal} from '../../components/datagrids/categories/DeleteCategoryModal';
import {EditPermissionsForm, EditPropertiesForm} from '../../components/categories/';

type Params = {
  categoryId: string;
};

const propertyTabName = '#pim_enrich-category-tab-property';
const historyTabName = '#pim_enrich-category-tab-history';
const permissionTabName = '#pim_enrich-category-tab-permission';

const CategoryEditPage: FC = () => {
  const {categoryId} = useParams<Params>();
  const translate = useTranslate();
  const router = useRouter();
  const userContext = useUserContext();
  const [categoryLabel, setCategoryLabel] = useState('');
  const [treeLabel, setTreeLabel] = useState('');
  const [tree, setTree] = useState<Category | null>(null);
  const [activeTab, setActiveTab] = useSessionStorageState(propertyTabName, 'pim_category_activeTab');
  const [isCurrent, switchTo] = useTabBar(activeTab);
  const {isGranted} = useSecurity();
  const [secondaryActionIsOpen, openSecondaryAction, closeSecondaryAction] = useBooleanState(false);
  const [isDeleteCategoryModalOpen, openDeleteCategoryModal, closeDeleteCategoryModal] = useBooleanState();
  const countProductsBeforeDeleteCategory = useCountProductsBeforeDeleteCategory(parseInt(categoryId));
  const [categoryToDelete, setCategoryToDelete] = useState<CategoryToDelete | null>(null);
  const {isCategoryDeletionPossible, handleDeleteCategory} = useDeleteCategory();
  const {
    category,
    categoryLoadingStatus,
    formData,
    onChangeCategoryLabel,
    onChangePermissions,
    onChangeApplyPermissionsOnChildren,
    thereAreUnsavedChanges,
    saveCategory,
  } = useEditCategoryForm(parseInt(categoryId));

  useSetPageTitle(translate('pim_title.pim_enrich_categorytree_edit', {'category.label': categoryLabel}));

  const uiLocale = userContext.get('uiLocale');

  const followSettingsIndex = () => router.redirect(router.generate('pim_settings_index'));
  const followCategoriesIndex = () => router.redirect(router.generate('pim_enrich_categorytree_index'));
  const followCategoryTree = () => {
    if (!tree) {
      return;
    }
    router.redirect(router.generate('pim_enrich_categorytree_tree', {id: tree.id}));
  };

  const handleCloseDeleteCategoryModal = () => {
    setCategoryToDelete(null);
    closeDeleteCategoryModal();
  };

  useEffect(() => {
    if (category === null) {
      setCategoryLabel('');
      setTreeLabel('');
      setTree(null);
      return;
    }

    const uiLocale = userContext.get('uiLocale');
    const rootCategory = category && category.root ? category.root : category;

    setCategoryLabel(
      category && category.labels.hasOwnProperty(uiLocale) ? category.labels[uiLocale] : `[${category.code}]`
    );
    setTreeLabel(
      rootCategory && rootCategory.labels.hasOwnProperty(uiLocale)
        ? rootCategory.labels[uiLocale]
        : `[${rootCategory.code}]`
    );
    setTree(rootCategory);
  }, [category]);

  if (categoryLoadingStatus === 'error') {
    return (
      <FullScreenError
        title={translate('error.exception', {status_code: '404'})}
        message={translate('pim_enrich.entity.category.content.edit.not_found')}
        code={404}
      />
    );
  }

  return (
    <>
      <PageHeader showPlaceholder={categoryLoadingStatus === 'idle' || categoryLoadingStatus === 'fetching'}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={followSettingsIndex}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step onClick={followCategoriesIndex}>
              {translate('pim_enrich.entity.category.plural_label')}
            </Breadcrumb.Step>
            <Breadcrumb.Step onClick={followCategoryTree}>{treeLabel || <BreadcrumbStepSkeleton />}</Breadcrumb.Step>
            <Breadcrumb.Step>{categoryLabel || <BreadcrumbStepSkeleton />}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          {isGranted('pim_enrich_product_category_remove') && (
            <Dropdown>
              <IconButton
                level="tertiary"
                title={translate('pim_common.other_actions')}
                icon={<MoreIcon />}
                ghost="borderless"
                onClick={openSecondaryAction}
              />
              {secondaryActionIsOpen && (
                <Dropdown.Overlay onClose={closeSecondaryAction}>
                  <Dropdown.Header>
                    <Dropdown.Title>{translate('pim_common.other_actions')}</Dropdown.Title>
                  </Dropdown.Header>
                  <Dropdown.ItemCollection>
                    <Dropdown.Item
                      onClick={() => {
                        countProductsBeforeDeleteCategory((nbProducts: number) => {
                          const identifier = parseInt(categoryId);
                          if (category && isCategoryDeletionPossible(category.labels[uiLocale], nbProducts)) {
                            setCategoryToDelete({
                              identifier,
                              label: category.labels[uiLocale],
                              onDelete: followCategoryTree,
                            });
                            openDeleteCategoryModal();
                          }
                        });
                      }}
                    >
                      <span>{translate('pim_common.delete')}</span>
                    </Dropdown.Item>
                  </Dropdown.ItemCollection>
                </Dropdown.Overlay>
              )}
            </Dropdown>
          )}
          <Button level="primary" onClick={saveCategory}>
            {translate('pim_common.save')}
          </Button>
        </PageHeader.Actions>
        <PageHeader.Title>{categoryLabel}</PageHeader.Title>
        <PageHeader.State>{thereAreUnsavedChanges && <UnsavedChanges />}</PageHeader.State>
      </PageHeader>
      <PageContent>
        <TabBar moreButtonTitle={'More'}>
          <TabBar.Tab
            isActive={isCurrent(propertyTabName)}
            onClick={() => {
              setActiveTab(propertyTabName);
              switchTo(propertyTabName);
            }}
          >
            {translate('pim_common.properties')}
          </TabBar.Tab>
          {formData && formData.permissions && isGranted('pimee_enrich_category_edit_permissions') && (
            <TabBar.Tab
              isActive={isCurrent(permissionTabName)}
              onClick={() => {
                setActiveTab(permissionTabName);
                switchTo(permissionTabName);
              }}
            >
              {translate('pim_common.permissions')}
            </TabBar.Tab>
          )}
          <TabBar.Tab
            isActive={isCurrent(historyTabName)}
            onClick={() => {
              setActiveTab(historyTabName);
              switchTo(historyTabName);
            }}
          >
            {translate('pim_common.history')}
          </TabBar.Tab>
        </TabBar>

        {isCurrent(propertyTabName) && category && (
          <EditPropertiesForm category={category} formData={formData} onChangeLabel={onChangeCategoryLabel} />
        )}
        {isCurrent(historyTabName) && (
          <HistoryPimView
            viewName="pim-category-edit-form-history"
            onBuild={(view: View) => {
              view.setData({categoryId});

              return Promise.resolve(view);
            }}
          />
        )}
        {isCurrent(permissionTabName) && (
          <EditPermissionsForm
            formData={formData}
            onChangePermissions={onChangePermissions}
            onChangeApplyPermissionsOnChildren={onChangeApplyPermissionsOnChildren}
          />
        )}
      </PageContent>
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
    </>
  );
};
export {CategoryEditPage};
