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
import {
  CategoryPageContent,
  EditAttributesForm,
  EditPermissionsForm,
  EditPropertiesForm,
  TemplateTitle,
} from '../components';
import {NoTemplateAttribute} from '../components/templates';

type Params = {
  categoryId: string;
};

enum Tabs {
  ATTRIBUTE = '#pim_enrich-category-tab-attribute',
  PROPERTY = '#pim_enrich-category-tab-property',
  HISTORY = '#pim_enrich-category-tab-history',
  PERMISSION = '#pim_enrich-category-tab-permission',
}

const CategoryEditPage: FC = () => {
  const {categoryId} = useParams<Params>();
  const translate = useTranslate();
  const router = useRouter();
  const userContext = useUserContext();

  // locales
  const [catalogLocale, setCatalogLocale] = useState<string | null>(null);
  const handleLocaleChanges = (locale: string) => {
    setCatalogLocale(locale);
    setLabels(locale);
  };

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

  // ui state
  const [activeTab, setActiveTab] = useSessionStorageState<string>(
    isGranted('pim_enrich_product_category_edit_attributes') ? Tabs.ATTRIBUTE : Tabs.PROPERTY,
    'pim_category_activeTab'
  );
  const [isCurrent, switchTo] = useTabBar(activeTab);
  const [secondaryActionIsOpen, openSecondaryAction, closeSecondaryAction] = useBooleanState(false);
  const [isDeleteCategoryModalOpen, openDeleteCategoryModal, closeDeleteCategoryModal] = useBooleanState();

  const handleSwitchTo = useCallback(
    (tab: string) => {
      setActiveTab(tab);
      switchTo(tab);
    },
    [setActiveTab, switchTo]
  );
  useSetPageTitle(translate('pim_title.pim_enrich_categorytree_edit', {'category.label': categoryLabel}));

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
    setLabels(catalogLocale);
  }, [category, userContext]);

  const setLabels = (locale: string) => {
    if (category) {
      setCategoryLabel(getLabel(category.properties.labels, locale, category.properties.code));

      let {root} = category;
      if (category.isRoot) {
        root = category;
      }
      if (root) {
        const {
          properties: {code, labels},
        } = root;
        setTreeLabel(getLabel(labels, locale, code));
        setTree(root);
        sessionStorage.setItem(
          'lastSelectedCategory',
          JSON.stringify({treeId: root.id.toString(), categoryId: category.id})
        );
      }
    }
  };

  useEffect(() => {
    if (category === null) return;

    if (
      activeTab === Tabs.ATTRIBUTE &&
      (!isGranted('pim_enrich_product_category_edit_attributes') || !category.template_uuid)
    ) {
      handleSwitchTo(Tabs.PROPERTY);
    }
  }, [category, activeTab]);

  if (categoryFetchingStatus === 'error') {
    return (
      <FullScreenError
        title={translate('error.exception', {status_code: '404'})}
        message={translate('pim_enrich.entity.category.content.edit.not_found')}
        code={404}
      />
    );
  }
  const templateHasAttribute = () => {
    return template?.attributes.length != 0;
  };

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
        {template && (
          <PageHeader.Content>
            <TemplateTitle template={template} locale={catalogLocale} />
          </PageHeader.Content>
        )}
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
                          if (category && isCategoryDeletionPossible(categoryLabel, nbProducts)) {
                            setCategoryToDelete({
                              identifier,
                              label: categoryLabel,
                              code: category.properties.code,
                              numberOfProducts: nbProducts,
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
      <CategoryPageContent>
        <TabBar moreButtonTitle={'More'} sticky={0}>
          {isGranted('pim_enrich_product_category_edit_attributes') && template && (
            <TabBar.Tab isActive={isCurrent(Tabs.ATTRIBUTE)} onClick={() => handleSwitchTo(Tabs.ATTRIBUTE)}>
              {translate('akeneo.category.attributes')}
            </TabBar.Tab>
          )}
          <TabBar.Tab isActive={isCurrent(Tabs.PROPERTY)} onClick={() => handleSwitchTo(Tabs.PROPERTY)}>
            {translate('pim_common.properties')}
          </TabBar.Tab>
          {category &&
            category.permissions &&
            permissionsAreEnabled &&
            isGranted('pimee_enrich_category_edit_permissions') && (
              <TabBar.Tab isActive={isCurrent(Tabs.PERMISSION)} onClick={() => handleSwitchTo(Tabs.PERMISSION)}>
                {translate('pim_common.permissions')}
              </TabBar.Tab>
            )}
          {isGranted('pim_enrich_product_category_history') && (
            <TabBar.Tab isActive={isCurrent(Tabs.HISTORY)} onClick={() => handleSwitchTo(Tabs.HISTORY)}>
              {translate('pim_common.history')}
            </TabBar.Tab>
          )}
        </TabBar>

        {isGranted('pim_enrich_product_category_edit_attributes') &&
          isCurrent(Tabs.ATTRIBUTE) &&
          category &&
          template &&
          !templateHasAttribute() && (
            <NoTemplateAttribute
              title={translate('akeneo.category.edition_form.template.no_attribute_title')}
              instructions={translate('akeneo.category.edition_form.template.no_attribute_instructions')}
            />
          )}
        {isGranted('pim_enrich_product_category_edit_attributes') &&
          isCurrent(Tabs.ATTRIBUTE) &&
          category &&
          template &&
          templateHasAttribute() && (
            <EditAttributesForm
              attributeValues={category.attributes}
              template={template}
              onAttributeValueChange={onChangeAttribute}
              onLocaleChange={handleLocaleChanges}
            />
          )}
        {isCurrent(Tabs.PROPERTY) && category && (
          <EditPropertiesForm category={category} onChangeLabel={onChangeCategoryLabel} />
        )}
        {isCurrent(Tabs.PERMISSION) && category && permissionsAreEnabled && (
          <EditPermissionsForm
            category={category}
            applyPermissionsOnChildren={applyPermissionsOnChildren}
            onChangePermissions={onChangePermissions}
            onChangeApplyPermissionsOnChildren={onChangeApplyPermissionsOnChildren}
          />
        )}
        {isCurrent(Tabs.HISTORY) && (
          <HistoryPimView
            viewName="pim-category-edit-form-history"
            onBuild={onBuildHistoryView}
            version={historyVersion}
          />
        )}
      </CategoryPageContent>
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
    </>
  );
};
export {CategoryEditPage};
