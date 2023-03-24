import React, {FC} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage, TemplatePage} from './pages';
import {EditCategoryProvider} from './components';
import {QueryClient, QueryClientProvider} from 'react-query';

type Props = {
  setCanLeavePage: (canLeavePage: boolean) => void;
};

const CategoriesApp: FC<Props> = ({setCanLeavePage}) => {
  const queryClient = new QueryClient();

  return (
    <Router basename="/enrich/product-category-tree">
      <Switch>
        <Route path="/:treeId/tree">
          <QueryClientProvider client={queryClient}>
            <CategoriesTreePage />
          </QueryClientProvider>
        </Route>
        <Route path="/:categoryId/edit">
          <EditCategoryProvider setCanLeavePage={setCanLeavePage}>
            <CategoryEditPage />
          </EditCategoryProvider>
        </Route>
        <Route path="/:treeId/template/:templateId">
          <QueryClientProvider client={queryClient}>
            <TemplatePage />
          </QueryClientProvider>
        </Route>
        <Route path="/">
          <QueryClientProvider client={queryClient}>
            <CategoriesIndex />
          </QueryClientProvider>
        </Route>
      </Switch>
    </Router>
  );
};

export {CategoriesApp};
