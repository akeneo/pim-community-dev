import React from 'react';
import {JobExecutionList} from './pages/JobExecutionList';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {JobExecutionDetail} from './pages/JobExecutionDetail';

const ProcessTrackerApp = () => (
  <Router basename="/rac-job">
    <Switch>
      <Route path="/show/:jobExecutionId">
        <JobExecutionDetail />
      </Route>
      <Route path="/">
        <JobExecutionList />
      </Route>
    </Switch>
  </Router>
);

export {ProcessTrackerApp};
