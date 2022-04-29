import React, {FC} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage} from './pages';
import {EditCategoryProvider} from './components';

type Props = {
  setCanLeavePage: (canLeavePage: boolean) => void;
};

const CategoriesApp: FC<Props> = ({setCanLeavePage}) => {
  return (
        <Router basename="/enrich/product-category-tree">
          <Switch>
            <Route path="/:treeId/tree">
              <CategoriesTreePage />
            </Route>
            <Route path="/:categoryId/edit">
              <EditCategoryProvider setCanLeavePage={setCanLeavePage}>
                <CategoryEditPage />
              </EditCategoryProvider>
            </Route>
            <Route path="/">
              <CategoriesIndex />
            </Route>
          </Switch>
        </Router>
  );
};

export {CategoriesApp};
