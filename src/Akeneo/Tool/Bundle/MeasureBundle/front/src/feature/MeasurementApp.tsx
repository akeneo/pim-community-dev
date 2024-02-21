import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {Edit} from './pages/edit';
import {List} from './pages/list';

const MeasurementApp = () => {
  return (
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
  );
};

export {MeasurementApp};
