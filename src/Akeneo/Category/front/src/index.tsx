import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes, Translations} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {CategoriesApp} from "./feature";
import {ConfigurationProvider, Page} from "./settings";
import {FakePIM} from './FakePIM';
import {HashRouter as Router, Route, Switch} from "react-router-dom";

ReactDOM.render(
  <ThemeProvider theme={pimTheme}>
    <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
      <ConfigurationProvider>
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
      </ConfigurationProvider>
    </MicroFrontendDependenciesProvider>
  </ThemeProvider>
  , document.getElementById('root')
);
