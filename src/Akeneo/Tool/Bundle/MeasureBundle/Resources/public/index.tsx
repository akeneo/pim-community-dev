import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {DependenciesProvider, DependenciesProviderProps} from 'akeneomeasure/DependenciesProvider';
import {AkeneoThemeProvider} from 'akeneomeasure/AkeneoThemeProvider';
import {List} from 'akeneomeasure/pages/list';
import {Edit} from 'akeneomeasure/pages/edit';

export default ({dependencies}: DependenciesProviderProps) => (
  <DependenciesProvider dependencies={dependencies}>
    <AkeneoThemeProvider>
      <Router basename="/configuration/measurement">
        <Switch>
          <Route path="/:measurementFamilyCode">
            <Edit />
          </Route>
          <Route path="/">
            <List />
          </Route>
        </Switch>
      </Router>
    </AkeneoThemeProvider>
  </DependenciesProvider>
);
