import React from 'react';
import {Route, HashRouter as Router, Switch} from 'react-router-dom';
import {ExecutionDetail} from './ExecutionDetail';

const Index = () => {
  return (
    <Router basename="/job/show">
      <Switch>
        <Route path="/:jobExecutionId">
          <ExecutionDetail />
        </Route>
      </Switch>
    </Router>
  );
};

export {Index};
