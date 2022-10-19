import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {Edit, List} from './controllers';
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
