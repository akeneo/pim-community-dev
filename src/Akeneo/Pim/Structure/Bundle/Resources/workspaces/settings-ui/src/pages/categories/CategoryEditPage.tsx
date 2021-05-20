import React, {FC, useEffect, useState} from 'react';
import {useParams} from 'react-router';
import {Breadcrumb, TabBar, useTabBar} from 'akeneo-design-system';
import {
  FullScreenError,
  PageContent,
  PageHeader,
  PimView,
  useRouter,
  useSetPageTitle,
  useTranslate,
  useUserContext,
  useSessionStorageState,
} from '@akeneo-pim-community/shared';
import {useCategory} from '../../hooks';
import {Category} from '../../models';
import {HistoryPimView, View} from './HistoryPimView';

type Params = {
  categoryId: string;
};

const propertyTabName = '#pim_enrich-category-tab-property';
const historyTabName = '#pim_enrich-category-tab-history';

const CategoryEditPage: FC = () => {
  const {categoryId} = useParams<Params>();
  const translate = useTranslate();
  const router = useRouter();
  const userContext = useUserContext();
  const {category, status, load} = useCategory(parseInt(categoryId));
  const [categoryLabel, setCategoryLabel] = useState(`[${categoryId}]`);
  const [treeLabel, setTreeLabel] = useState(translate('pim_enrich.entity.category.content.edit.default_tree_label'));
  const [tree, setTree] = useState<Category | null>(null);
  const [activeTab, setActiveTab] = useSessionStorageState(propertyTabName, 'pim_category_activeTab');
  const [isCurrent, switchTo] = useTabBar(activeTab);

  useSetPageTitle(translate('pim_title.pim_enrich_categorytree_edit', {'category.label': categoryLabel}));

  const followSettingsIndex = () => router.redirect(router.generate('pim_enrich_attribute_index'));
  const followCategoriesIndex = () => router.redirect(router.generate('pim_enrich_categorytree_index'));
  const followCategoryTree = () => {
    if (!tree) {
      return;
    }
    router.redirect(router.generate('pim_enrich_categorytree_tree', {id: tree.id}));
  };

  useEffect(() => {
    load();
  }, [categoryId]);

  useEffect(() => {
    const rootCategory = category && category.root ? category.root : category;
    const uiLocale = userContext.get('uiLocale');

    setCategoryLabel(
      category && category.labels.hasOwnProperty(uiLocale) ? category.labels[uiLocale] : `[${categoryId}]`
    );
    setTreeLabel(
      rootCategory
        ? rootCategory.labels[userContext.get('uiLocale')]
        : translate('pim_enrich.entity.category.content.edit.default_tree_label')
    );
    setTree(rootCategory);
  }, [category]);

  if (status === 'error') {
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
      <PageContent>
        <TabBar moreButtonTitle={'More'}>
          <TabBar.Tab
            isActive={isCurrent(propertyTabName)}
            onClick={() => {
              setActiveTab(propertyTabName);
              switchTo(propertyTabName)();
            }}
          >
            {translate('pim_common.properties')}
          </TabBar.Tab>
          <TabBar.Tab
            isActive={isCurrent(historyTabName)}
            onClick={() => {
              setActiveTab(historyTabName);
              switchTo(historyTabName)();
            }}
          >
            {translate('pim_common.history')}
          </TabBar.Tab>
        </TabBar>

        {isCurrent(propertyTabName) && <div>{translate('pim_common.properties')}</div>}
        {isCurrent(historyTabName) && (
          <HistoryPimView
            viewName="pim-category-edit-form-history"
            onBuild={(view: View) => {
              view.setData({categoryId});

              return Promise.resolve(view);
            }}
          />
        )}
      </PageContent>
    </>
  );
};
export {CategoryEditPage};
