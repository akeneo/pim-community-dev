import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {Route, HashRouter as Router, Switch} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {ExecutionDetail} from './ExecutionDetail';

const Index = () => {
  return (
    <ThemeProvider theme={pimTheme}>
      <Router basename="/job/show">
        <Switch>
          <Route path="/:jobExecutionId">
            <ExecutionDetail />
          </Route>
        </Switch>
      </Router>
    </ThemeProvider>
  );
};

export {Index};
