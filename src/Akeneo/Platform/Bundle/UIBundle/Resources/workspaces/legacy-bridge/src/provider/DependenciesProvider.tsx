import React, {createContext, FC} from 'react';
import {dependencies} from './dependencies';
import {Notify, Router, Security, Translate, UserContext, ViewBuilder, Mediator} from './DependenciesProvider.type';

type DependenciesContextProps = {
  notify?: Notify;
  router?: Router;
  security?: Security;
  translate?: Translate;
  user?: UserContext;
  viewBuilder?: ViewBuilder;
  mediator?: Mediator;
};

const DependenciesContext = createContext<DependenciesContextProps>({});

const DependenciesProvider: FC = ({children}) => {
  const value = {
    notify: dependencies.notify,
    router: dependencies.router,
    security: dependencies.security,
    translate: dependencies.translate,
    user: dependencies.user,
    viewBuilder: dependencies.viewBuilder,
    mediator: dependencies.mediator,
  };

  return <DependenciesContext.Provider value={value}>{children}</DependenciesContext.Provider>;
};

export {DependenciesProvider, DependenciesContextProps, DependenciesContext};
