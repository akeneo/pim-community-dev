import React, {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes, Translations} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {CategoriesApp} from './feature';
import {ConfigurationProvider, Page as ConfigurationPage} from './configuration';
import {FakePIM} from './FakePIM';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';

ReactDOM.render(
  <StrictMode>
    <ThemeProvider theme={pimTheme}>
      <ConfigurationProvider>
        <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
          <FakePIM>
            <Router basename="/">
              <Switch>
                <Route path="/configuration">
                  <ConfigurationPage />
                </Route>
                <Route path="/">
                  <CategoriesApp setCanLeavePage={() => true} />
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
