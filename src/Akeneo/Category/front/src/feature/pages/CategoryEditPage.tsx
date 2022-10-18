import React, {FC, useCallback, useEffect, useState} from 'react';
import {useParams} from 'react-router';
import {
  Breadcrumb,
  Button,
  Dropdown,
  IconButton,
  MoreIcon,
  SkeletonPlaceholder,
  TabBar,
  useBooleanState,
  useTabBar,
} from 'akeneo-design-system';
import {
  FullScreenError,
  getLabel,
  PageContent,
  PageHeader,
  PimView,
  UnsavedChanges,
  useFeatureFlags,
  useRouter,
  useSecurity,
  useSessionStorageState,
  useSetPageTitle,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {CategoryToDelete, useCountProductsBeforeDeleteCategory, useDeleteCategory, useEditCategoryForm} from '../hooks';
import {EnrichCategory} from '../models';
import {HistoryPimView, View} from './HistoryPimView';
import {DeleteCategoryModal} from '../components/datagrids/DeleteCategoryModal';
import {EditAttributesForm, EditPermissionsForm, EditPropertiesForm} from '../components';

type Params = {
  categoryId: string;
};

const propertyTabName = '#pim_enrich-category-tab-property';
const attributeTabName = '#pim_enrich-category-tab-attribute';
const historyTabName = '#pim_enrich-category-tab-history';
const permissionTabName = '#pim_enrich-category-tab-permission';

const CategoryEditPage: FC = () => {
  const {categoryId} = useParams<Params>();
  const translate = useTranslate();
  const router = useRouter();
  const userContext = useUserContext();

  // features
  const featureFlags = useFeatureFlags();
  const permissionsAreEnabled = featureFlags.isEnabled('permission');

  const {isGranted} = useSecurity();
  const {isCategoryDeletionPossible, handleDeleteCategory} = useDeleteCategory();

  // data
  const [categoryLabel, setCategoryLabel] = useState('');
  const [treeLabel, setTreeLabel] = useState('');
  const countProductsBeforeDeleteCategory = useCountProductsBeforeDeleteCategory(parseInt(categoryId));
  const [categoryToDelete, setCategoryToDelete] = useState<CategoryToDelete | null>(null);
  const [tree, setTree] = useState<EnrichCategory | null>(null);

  // ui state
  const [activeTab, setActiveTab] = useSessionStorageState(propertyTabName, 'pim_category_activeTab');
  const [isCurrent, switchTo] = useTabBar(activeTab);
  if (activeTab === attributeTabName && !isGranted('pim_enrich_product_category_edit_attributes')) {
    setActiveTab(propertyTabName);
    switchTo(propertyTabName);
  }
  const [secondaryActionIsOpen, openSecondaryAction, closeSecondaryAction] = useBooleanState(false);
  const [isDeleteCategoryModalOpen, openDeleteCategoryModal, closeDeleteCategoryModal] = useBooleanState();

  const {
    category,
    template,
    categoryFetchingStatus,
    applyPermissionsOnChildren,
    onChangeCategoryLabel,
    onChangePermissions,
    onChangeAttribute,
    onChangeApplyPermissionsOnChildren,
    isModified: thereAreUnsavedChanges,
    saveCategory,
    historyVersion,
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

  const onBuildHistoryView = useCallback(
    async (view: View) => {
      view.setData({categoryId});
      return view;
    },
    [categoryId]
  );

  useEffect(() => {
    if (!category) {
      setCategoryLabel('');
      setTreeLabel('');
      setTree(null);
      return;
    }

    const catalogLocale = userContext.get('catalogLocale');

    setCategoryLabel(getLabel(category.properties.labels, catalogLocale, category.properties.code));

    let {root} = category;
    if (category.isRoot) {
      root = category;
    }
    if (root) {
      const {
        properties: {code, labels},
      } = root;
      setTreeLabel(getLabel(labels, catalogLocale, code));
      setTree(root);
      sessionStorage.setItem(
        'lastSelectedCategory',
        JSON.stringify({treeId: root.id.toString(), categoryId: category.id})
      );
    }
  }, [category, userContext]);

  if (categoryFetchingStatus === 'error') {
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
      <PageHeader showPlaceholder={categoryFetchingStatus === 'idle' || categoryFetchingStatus === 'fetching'}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={followSettingsIndex}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step onClick={followCategoriesIndex}>
              {translate('pim_enrich.entity.category.plural_label')}
            </Breadcrumb.Step>
            <Breadcrumb.Step onClick={followCategoryTree}>
              {treeLabel || <SkeletonPlaceholder as="span">{categoryId}</SkeletonPlaceholder>}
            </Breadcrumb.Step>
            <Breadcrumb.Step>
              {categoryLabel || <SkeletonPlaceholder as="span">{categoryId}</SkeletonPlaceholder>}
            </Breadcrumb.Step>
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
                className="dropdown-button"
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
                          if (
                            category &&
                            isCategoryDeletionPossible(category.properties.labels[uiLocale], nbProducts)
                          ) {
                            setCategoryToDelete({
                              identifier,
                              label: category.properties.labels[uiLocale],
                              onDelete: followCategoryTree,
                            });
                            openDeleteCategoryModal();
                          }
                        });
                        closeSecondaryAction();
                      }}
                      className="AknDropdown-menuLink"
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
        <PageHeader.Title>{categoryLabel ?? categoryId}</PageHeader.Title>
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
          {isGranted('pim_enrich_product_category_edit_attributes') && (
            <TabBar.Tab
              isActive={isCurrent(attributeTabName)}
              onClick={() => {
                setActiveTab(attributeTabName);
                switchTo(attributeTabName);
              }}
            >
              {translate('akeneo.category.attributes')}
            </TabBar.Tab>
          )}
          {category &&
            category.permissions &&
            permissionsAreEnabled &&
            isGranted('pimee_enrich_category_edit_permissions') && (
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
          {isGranted('pim_enrich_product_category_history') && (
            <TabBar.Tab
              isActive={isCurrent(historyTabName)}
              onClick={() => {
                setActiveTab(historyTabName);
                switchTo(historyTabName);
              }}
            >
              {translate('pim_common.history')}
            </TabBar.Tab>
          )}
        </TabBar>

        {isCurrent(propertyTabName) && category && (
          <EditPropertiesForm category={category} onChangeLabel={onChangeCategoryLabel} />
        )}
        {isGranted('pim_enrich_product_category_edit_attributes') &&
          isCurrent(attributeTabName) &&
          category &&
          template && (
            <EditAttributesForm
              attributeValues={category.attributes}
              template={template}
              onAttributeValueChange={onChangeAttribute}
            />
          )}
        {isCurrent(historyTabName) && (
          <HistoryPimView
            viewName="pim-category-edit-form-history"
            onBuild={onBuildHistoryView}
            version={historyVersion}
          />
        )}
        {isCurrent(permissionTabName) && category && permissionsAreEnabled && (
          <EditPermissionsForm
            category={category}
            applyPermissionsOnChildren={applyPermissionsOnChildren}
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
