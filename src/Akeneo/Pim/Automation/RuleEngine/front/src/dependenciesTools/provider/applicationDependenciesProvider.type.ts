import { View } from "backbone";

enum NotificationLevel {
  INFO = "info",
  SUCCESS = "success",
  WARNING = "warning",
  ERROR = "error"
}

type Notify = (level: NotificationLevel, message: string) => void;

type Router = {
  generate: (route: string, parameters?: { [param: string]: string }) => string;
  redirect: (fragment: string, options?: object) => void;
};

type Security = { isGranted: (acl: string) => boolean };

type Translate = (
  id: string,
  placeholders?: { [name: string]: string },
  count?: number
) => string;

type User = {
  get: (data: string) => string;
  set: (key: string, value: string, options: {}) => void;
};

type ViewBuilder = {
  build(viewName: string): Promise<View>;
};

export {
  NotificationLevel,
  Notify,
  Router,
  Security,
  Translate,
  User,
  ViewBuilder
};
