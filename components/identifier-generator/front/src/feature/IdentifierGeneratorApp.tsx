import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {List} from './pages/List';
import {Edit} from './pages/Edit';
import {QueryClient, QueryClientProvider} from 'react-query';

const queryClient = new QueryClient();

const IdentifierGeneratorApp: React.FC = () => {
  return (
    <QueryClientProvider client={queryClient}>
      <Router basename="/configuration/identifier-generator">
        <Switch>
          <Route path="/:identifierGeneratorCode">
            <Edit />
          </Route>
          <Route path="/">
            <List />
          </Route>
        </Switch>
      </Router>
    </QueryClientProvider>
  );
};

export {IdentifierGeneratorApp};
