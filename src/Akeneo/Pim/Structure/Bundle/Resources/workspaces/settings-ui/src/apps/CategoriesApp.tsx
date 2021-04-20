import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage} from '../pages';

const CategoriesApp: FC = () => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <Router basename="/enrich/product-category-tree">
          <Switch>
            <Route path="/:treeId/tree">
              <CategoriesTreePage />
            </Route>
            <Route path="/:categoryId/edit">
              <CategoryEditPage />
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
