import React from 'react';
import {Route, HashRouter as Router, Switch} from 'react-router-dom';
import {ExecutionDetail} from './ExecutionDetail';

const Index = () => {
  return (
    <Router>
      <Switch>
        <Route path="/job/show/:jobExecutionId">
          <ExecutionDetail />
        </Route>
      </Switch>
    </Router>
  );
};

export {Index};
