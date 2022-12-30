import React from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {Edit, List} from './controllers';
import {QueryClient, QueryClientProvider} from 'react-query';
import {IdentifierGeneratorAclContextProvider} from './context/IdentifierGeneratorAclContextProvider';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: false,
      staleTime: Infinity,
    },
  },
});

const IdentifierGeneratorApp: React.FC = () => (
  <QueryClientProvider client={queryClient}>
    <IdentifierGeneratorAclContextProvider>
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
    </IdentifierGeneratorAclContextProvider>
  </QueryClientProvider>
);

export {IdentifierGeneratorApp};
