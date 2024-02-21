import {ReactElement, ReactNode} from 'react';
import {IconProps} from 'akeneo-design-system';

enum NotificationLevel {
  INFO = 'info',
  SUCCESS = 'success',
  WARNING = 'warning',
  ERROR = 'error',
}

type Notify = (level: NotificationLevel, message: string, children?: ReactNode, icon?: ReactElement<IconProps>) => void;

type RouteParams = {[param: string]: any};

type Router = {
  generate: (route: string, parameters?: RouteParams) => string;
  redirect: (fragment: string, options?: object) => void;
  redirectToRoute: (route: string, parameters?: RouteParams) => void;
};

type Security = {isGranted: (acl: string) => boolean};

type Translate = (id: string, placeholders?: {[name: string]: string | number}, count?: number) => string;

type UserContext = {
  get: (data: string) => string;
  set: (key: string, value: string, options: {}) => void;
};

type View = {
  setElement: (element: HTMLElement | null) => View;
  render: () => void;
  remove: () => void;
  setData: (data: any, options?: {silent?: boolean}) => void;
};

type ViewBuilder = {
  build(viewName: string): Promise<View>;
};

type Mediator = {
  trigger(event: string, args?: any): void;
  on(event: string, callback: () => void): void;
  off(event: string, callback: () => void): void;
};

type FeatureFlags = {
  isEnabled(feature: string): boolean;
};

type Analytics = {
  track(event: string, properties?: object): void;
  appcuesTrack(event: string, properties?: object): void;
};

type SystemConfiguration = {
  initialize: () => Promise<void>;
  refresh: () => Promise<void>;
  get: (key: string, defaultValue?: string | number | boolean | null) => string | number | boolean | null;
};

export {NotificationLevel};
export type {
  Analytics,
  FeatureFlags,
  Mediator,
  Notify,
  RouteParams,
  Router,
  Security,
  SystemConfiguration,
  Translate,
  UserContext,
  View,
  ViewBuilder,
};
