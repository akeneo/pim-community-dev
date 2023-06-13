import React, {FC} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage, TemplatePage} from './pages';
import {CanLeavePageProvider, EditCategoryProvider} from './components';
import {QueryClient, QueryClientProvider} from 'react-query';

type Props = {
  setCanLeavePage: (canLeavePage: boolean) => void;
  setLeavePageMessage: (leavePageMessage: string) => void;
};

const CategoriesApp: FC<Props> = ({setCanLeavePage, setLeavePageMessage}) => {
  const queryClient = new QueryClient();

  return (
    <QueryClientProvider client={queryClient}>
      <CanLeavePageProvider setCanLeavePage={setCanLeavePage} setLeavePageMessage={setLeavePageMessage}>
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
      </CanLeavePageProvider>
    </QueryClientProvider>
  );
};

export {CategoriesApp};
