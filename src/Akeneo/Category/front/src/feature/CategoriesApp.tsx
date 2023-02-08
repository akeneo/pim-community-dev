import React, {FC} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage} from './pages';
import {EditCategoryProvider} from './components';
import {useFeatureFlags} from '@akeneo-pim-community/shared';
import {LegacyCategoryEditPage} from './legacy/pages/LegacyCategoryEditPage';
import {TemplatePage} from './pages';

type Props = {
  setCanLeavePage: (canLeavePage: boolean) => void;
};

const CategoriesApp: FC<Props> = ({setCanLeavePage}) => {
  const featureFlags = useFeatureFlags();

  return (
    <Router basename="/enrich/product-category-tree">
      <h1>Test JBE</h1>
      <Switch>
        <Route path="/:treeId/tree">
          <CategoriesTreePage />
        </Route>
        <Route path="/:categoryId/edit">
          <EditCategoryProvider setCanLeavePage={setCanLeavePage}>
            {featureFlags.isEnabled('enriched_category') ? <CategoryEditPage /> : <LegacyCategoryEditPage />}
          </EditCategoryProvider>
        </Route>
        {featureFlags.isEnabled('enriched_category') && (
          <Route path="/:treeId/template/:templateId">
            <EditCategoryProvider setCanLeavePage={setCanLeavePage}>
              <TemplatePage />
            </EditCategoryProvider>
          </Route>
        )}
        <Route path="/">
          <CategoriesIndex />
        </Route>
      </Switch>
    </Router>
  );
};

export {CategoriesApp};
