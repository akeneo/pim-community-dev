import {createContext} from 'react';
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

export {DependenciesContext};
export type {DependenciesContextProps};
