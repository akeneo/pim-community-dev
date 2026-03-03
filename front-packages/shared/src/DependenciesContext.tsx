import {createContext} from 'react';
import {
  Notify,
  Router,
  Security,
  Translate,
  UserContext,
  ViewBuilder,
  Mediator,
  FeatureFlags,
  Analytics,
  SystemConfiguration,
} from './DependenciesProvider.type';

type DependenciesContextProps = {
  notify?: Notify;
  router?: Router;
  security?: Security;
  translate?: Translate;
  user?: UserContext;
  viewBuilder?: ViewBuilder;
  mediator?: Mediator;
  featureFlags?: FeatureFlags;
  analytics?: Analytics;
  systemConfiguration?: SystemConfiguration;
};

const DependenciesContext = createContext<DependenciesContextProps>({});

export type {DependenciesContextProps};
export {DependenciesContext};
