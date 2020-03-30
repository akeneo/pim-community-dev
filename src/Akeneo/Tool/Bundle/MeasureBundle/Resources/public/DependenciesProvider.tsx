import React, {PropsWithChildren, StrictMode} from 'react';
import {LegacyContext, LegacyContextValue} from 'akeneomeasure/context/legacy-context';
import {TranslateContext, TranslateContextValue} from 'akeneomeasure/context/translate-context';
import {UserContext, UserContextValue} from 'akeneomeasure/context/user-context';
import {RouterContext, RouterContextValue} from 'akeneomeasure/context/router-context';
import {NotifyContext, NotifyContextValue} from 'akeneomeasure/context/notify-context';
import {UnsavedChangesContextValue, UnsavedChangesContext} from 'akeneomeasure/context/unsaved-changes-context';
import {SecurityContext, SecurityContextValue} from 'akeneomeasure/context/security-context';
import {ConfigContext, ConfigContextValue} from 'akeneomeasure/context/config-context';

type DependenciesProviderProps = {
  dependencies: {
    legacy: LegacyContextValue;
    translate: TranslateContextValue;
    unsavedChanges: UnsavedChangesContextValue;
    user: UserContextValue;
    router: RouterContextValue;
    notify: NotifyContextValue;
    security: SecurityContextValue;
    config: ConfigContextValue;
  };
};

const DependenciesProvider = ({dependencies, children}: PropsWithChildren<DependenciesProviderProps>) => (
  <StrictMode>
    <TranslateContext.Provider value={dependencies.translate}>
      <SecurityContext.Provider value={dependencies.security}>
        <LegacyContext.Provider value={dependencies.legacy}>
          <UnsavedChangesContext.Provider value={dependencies.unsavedChanges}>
            <UserContext.Provider value={dependencies.user}>
              <RouterContext.Provider value={dependencies.router}>
                <NotifyContext.Provider value={dependencies.notify}>
                  <ConfigContext.Provider value={dependencies.config}>
                    {children}
                  </ConfigContext.Provider>
                </NotifyContext.Provider>
              </RouterContext.Provider>
            </UserContext.Provider>
          </UnsavedChangesContext.Provider>
        </LegacyContext.Provider>
      </SecurityContext.Provider>
    </TranslateContext.Provider>
  </StrictMode>
);

export {DependenciesProvider, DependenciesProviderProps};
