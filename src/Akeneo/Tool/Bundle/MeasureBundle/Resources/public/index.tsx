import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {DependenciesProvider, DependenciesProviderProps} from 'akeneomeasure/DependenciesProvider';
import {AkeneoThemeProvider} from 'akeneomeasure/AkeneoThemeProvider';
import {List} from 'akeneomeasure/pages/list';
import {Edit} from 'akeneomeasure/pages/edit';

export default ({dependencies}: DependenciesProviderProps) => (
  <DependenciesProvider dependencies={dependencies}>
    <AkeneoThemeProvider>
      <Router>
        <Switch>
          <Route path="/configuration/measurement/:measurementFamilyCode">
            <Edit />
          </Route>
          <Route path="/configuration/measurement/">
            <List />
          </Route>
        </Switch>
      </Router>
    </AkeneoThemeProvider>
  </DependenciesProvider>
);
