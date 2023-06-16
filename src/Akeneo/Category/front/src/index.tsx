import {MicroFrontendDependenciesProvider, Routes} from '@akeneo-pim-community/shared';
import {pimTheme} from 'akeneo-design-system';
import React, {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import {Route, HashRouter as Router, Switch} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {FakePIM} from './FakePIM';
import {Page as ConfigurationPage, ConfigurationProvider} from './configuration';
import {CategoriesApp} from './feature';
import {routes} from './routes.json';

ReactDOM.render(
  <StrictMode>
    <ThemeProvider theme={pimTheme}>
      <ConfigurationProvider>
        <MicroFrontendDependenciesProvider routes={routes as Routes}>
          <FakePIM>
            <Router basename="/">
              <Switch>
                <Route path="/configuration">
                  <ConfigurationPage />
                </Route>
                <Route path="/">
                  <CategoriesApp
                    setCanLeavePage={canLeavePage => console.debug('Can leave page:', canLeavePage)}
                    setLeavePageMessage={leavePageMessage => console.debug('Leave page message:', leavePageMessage)}
                  />
                </Route>
              </Switch>
            </Router>
          </FakePIM>
        </MicroFrontendDependenciesProvider>
      </ConfigurationProvider>
    </ThemeProvider>
  </StrictMode>,
  document.getElementById('root')
);
