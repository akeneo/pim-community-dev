import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage} from '../pages';
import {EditCategoryProvider} from "../components";

type Props = {
  setCanLeavePage: (canLeavePage: boolean) => void;
};

const CategoriesApp: FC<Props> = ({setCanLeavePage}) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <Router basename="/enrich/product-category-tree">
          <Switch>
            <Route path="/:treeId/tree">
              <CategoriesTreePage />
            </Route>
            <Route path="/:categoryId/edit">
              <EditCategoryProvider setCanLeavePage={setCanLeavePage} >
                <CategoryEditPage />
              </EditCategoryProvider>
            </Route>
            <Route path="/">
              <CategoriesIndex />
            </Route>
          </Switch>
        </Router>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {CategoriesApp};
