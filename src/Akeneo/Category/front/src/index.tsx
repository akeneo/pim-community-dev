import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Routes, Translations} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {CategoriesApp} from "./feature";
import {ConfigurationProvider, Page} from "./configuration";
import {MicroFrontendDependenciesProvider} from "./microfrontend";
import {FakePIM} from './FakePIM';
import {HashRouter as Router, Route, Switch} from "react-router-dom";

ReactDOM.render(
  <ThemeProvider theme={pimTheme}>
    <ConfigurationProvider>
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
          <FakePIM>
            <Router basename="/">
              <Switch>
                <Route path="/configuration">
                  <Page/>
                </Route>
                <Route path="/">
                  <CategoriesApp setCanLeavePage={() => true}/>
                </Route>
              </Switch>
            </Router>
          </FakePIM>
      </MicroFrontendDependenciesProvider>
    </ConfigurationProvider>
  </ThemeProvider>
  , document.getElementById('root')
);
