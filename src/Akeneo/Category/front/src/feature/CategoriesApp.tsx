import React, {FC} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage, TemplatePage} from './pages';
import {EditCategoryProvider} from './components';
import {QueryClient, QueryClientProvider} from 'react-query';

const CategoriesApp: FC = () => {
  const queryClient = new QueryClient();

  return (
    <QueryClientProvider client={queryClient}>
      <Router basename="/enrich/product-category-tree">
        <Switch>
          <Route path="/:treeId/tree">
            <CategoriesTreePage />
          </Route>
          <Route path="/:categoryId/edit">
            <EditCategoryProvider>
              <CategoryEditPage />
            </EditCategoryProvider>
          </Route>
          <Route path="/:treeId/template/:templateId">
            <TemplatePage />
          </Route>
          <Route path="/">
            <CategoriesIndex />
          </Route>
        </Switch>
      </Router>
    </QueryClientProvider>
  );
};

export {CategoriesApp};
