import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {DependenciesProvider, DependenciesProviderProps} from 'akeneomeasure/DependenciesProvider';
import {AkeneoThemeProvider} from 'akeneomeasure/AkeneoThemeProvider';
import {List} from 'akeneomeasure/pages/list';

export default ({dependencies}: DependenciesProviderProps) => (
  <DependenciesProvider dependencies={dependencies}>
    <AkeneoThemeProvider>
      <Router>
        <Switch>
          <Route path="/configuration/measurement/">
            <List />
          </Route>
        </Switch>
      </Router>
    </AkeneoThemeProvider>
  </DependenciesProvider>
);
