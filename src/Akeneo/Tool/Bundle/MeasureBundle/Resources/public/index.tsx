import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneomeasure/shared/theme';
import {Index} from 'akeneomeasure/pages/Index';
import {LegacyContext, LegacyContextValue} from 'akeneomeasure/context/legacy-context';
import {TranslateContext, TranslateContextValue} from 'akeneomeasure/context/translate-context';
import {UserContext, UserContextValue} from 'akeneomeasure/context/user-context';

interface Props {
  dependencies: {
    legacy: LegacyContextValue;
    translate: TranslateContextValue;
    user: UserContextValue;
  };
}

export default ({dependencies}: Props) => (
  <StrictMode>
    <TranslateContext.Provider value={dependencies.translate}>
      <LegacyContext.Provider value={dependencies.legacy}>
        <UserContext.Provider value={dependencies.user}>
          <ThemeProvider theme={akeneoTheme}>
            <Router>
              <Switch>
                <Route path="/configuration/measurement">
                  <Index />
                </Route>
              </Switch>
            </Router>
          </ThemeProvider>
        </UserContext.Provider>
      </LegacyContext.Provider>
    </TranslateContext.Provider>
  </StrictMode>
);
