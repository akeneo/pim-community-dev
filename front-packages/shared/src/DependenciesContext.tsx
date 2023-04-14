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
  AppcuesAnalytics,
  Analytics,
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
  appcuesAnalytics?: AppcuesAnalytics;
  analytics?: Analytics;
};

const DependenciesContext = createContext<DependenciesContextProps>({});

export type {DependenciesContextProps};
export {DependenciesContext};
