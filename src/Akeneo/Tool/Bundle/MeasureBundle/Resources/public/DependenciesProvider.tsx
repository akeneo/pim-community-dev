import React, {PropsWithChildren, StrictMode} from 'react';
import {LegacyContext, LegacyContextValue} from 'akeneomeasure/context/legacy-context';
import {TranslateContext, TranslateContextValue} from 'akeneomeasure/context/translate-context';
import {UserContext, UserContextValue} from 'akeneomeasure/context/user-context';
import {RouterContext, RouterContextValue} from 'akeneomeasure/context/router-context';
import {NotifyContext, NotifyContextValue} from 'akeneomeasure/context/notify-context';

type DependenciesProviderProps = {
  dependencies: {
    legacy: LegacyContextValue;
    translate: TranslateContextValue;
    user: UserContextValue;
    router: RouterContextValue;
    notify: NotifyContextValue;
  };
};

const DependenciesProvider = ({dependencies, children}: PropsWithChildren<DependenciesProviderProps>) => (
  <StrictMode>
    <TranslateContext.Provider value={dependencies.translate}>
      <LegacyContext.Provider value={dependencies.legacy}>
        <UserContext.Provider value={dependencies.user}>
          <RouterContext.Provider value={dependencies.router}>
            <NotifyContext.Provider value={dependencies.notify}>
              {children}
            </NotifyContext.Provider>
          </RouterContext.Provider>
        </UserContext.Provider>
      </LegacyContext.Provider>
    </TranslateContext.Provider>
  </StrictMode>
);

export {DependenciesProvider, DependenciesProviderProps};
