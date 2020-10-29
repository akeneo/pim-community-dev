import React, {createContext} from 'react';
import {dependencies} from './dependencies';
import {
  Notify,
  Router,
  Security,
  Translate,
  UserContext,
  ViewBuilder,
} from './applicationDependenciesProvider.type';

type ApplicationDependenciesContextProps = {
  notify?: Notify;
  router?: Router;
  security?: Security;
  translate?: Translate;
  user?: UserContext;
  viewBuilder?: ViewBuilder;
};

const ApplicationDependenciesContext = createContext<
  ApplicationDependenciesContextProps
>({});

const ApplicationDependenciesProvider: React.FC = ({children}) => {
  const value = {
    notify: dependencies.notify,
    router: dependencies.router,
    security: dependencies.security,
    translate: dependencies.translate,
    user: dependencies.user,
    viewBuilder: dependencies.viewBuilder,
  };
  return (
    <ApplicationDependenciesContext.Provider value={value}>
      {children}
    </ApplicationDependenciesContext.Provider>
  );
};

export {
  ApplicationDependenciesProvider,
  ApplicationDependenciesContextProps,
  ApplicationDependenciesContext,
};
