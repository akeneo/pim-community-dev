import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {JobExecutionList} from './pages/JobExecutionList';
import {JobExecutionDetail} from './pages/JobExecutionDetail';

const ProcessTrackerApp = () => (
  <Router basename="/job">
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
