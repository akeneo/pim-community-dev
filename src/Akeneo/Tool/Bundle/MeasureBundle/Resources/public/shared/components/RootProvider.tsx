import React, {StrictMode, PropsWithChildren} from 'react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneomeasure/shared/theme';
import {LegacyContext, LegacyContextValue} from 'akeneomeasure/context/legacy-context';
import {TranslateContext, TranslateContextValue} from 'akeneomeasure/context/translate-context';
import {UserContext, UserContextValue} from 'akeneomeasure/context/user-context';
import {RouterContextValue, RouterContext} from 'akeneomeasure/context/router-context';

type RootProviderProps = {
  dependencies: {
    legacy: LegacyContextValue;
    translate: TranslateContextValue;
    user: UserContextValue;
    router: RouterContextValue;
  };
};

const RootProvider = ({dependencies, children}: PropsWithChildren<RootProviderProps>) => (
  <StrictMode>
    <TranslateContext.Provider value={dependencies.translate}>
      <LegacyContext.Provider value={dependencies.legacy}>
        <UserContext.Provider value={dependencies.user}>
          <RouterContext.Provider value={dependencies.router}>
            <ThemeProvider theme={akeneoTheme}>{children}</ThemeProvider>
          </RouterContext.Provider>
        </UserContext.Provider>
      </LegacyContext.Provider>
    </TranslateContext.Provider>
  </StrictMode>
);

export {RootProviderProps, RootProvider};
