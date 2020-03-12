import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneomeasure/shared/theme';
import {List} from 'akeneomeasure/pages/list';
import {LegacyContext, LegacyContextValue} from 'akeneomeasure/context/legacy-context';
import {TranslateContext, TranslateContextValue} from 'akeneomeasure/context/translate-context';
import {UserContext, UserContextValue} from 'akeneomeasure/context/user-context';
import {RouterContextValue, RouterContext} from 'akeneomeasure/context/router-context';

interface Props {
  dependencies: {
    legacy: LegacyContextValue;
    translate: TranslateContextValue;
    user: UserContextValue;
    router: RouterContextValue;
  };
}

export default ({dependencies}: Props) => (
  <StrictMode>
    <TranslateContext.Provider value={dependencies.translate}>
      <LegacyContext.Provider value={dependencies.legacy}>
        <UserContext.Provider value={dependencies.user}>
          <RouterContext.Provider value={dependencies.router}>
            <ThemeProvider theme={akeneoTheme}>
              <Router>
                <Switch>
                  <Route path="/configuration/measurement">
                    <List />
                  </Route>
                </Switch>
              </Router>
            </ThemeProvider>
          </RouterContext.Provider>
        </UserContext.Provider>
      </LegacyContext.Provider>
    </TranslateContext.Provider>
  </StrictMode>
);
