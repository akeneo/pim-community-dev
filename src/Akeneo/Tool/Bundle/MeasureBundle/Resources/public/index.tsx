import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {RootProvider, RootProviderProps} from 'akeneomeasure/shared/components/RootProvider';
import {List} from 'akeneomeasure/pages/list';

export default ({dependencies}: RootProviderProps) => (
  <RootProvider dependencies={dependencies}>
    <Router>
      <Switch>
        <Route path="/configuration/measurement/">
          <List />
        </Route>
      </Switch>
    </Router>
  </RootProvider>
);
