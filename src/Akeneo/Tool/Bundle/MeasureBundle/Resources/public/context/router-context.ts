import {createContext} from 'react';

export interface RouterContextValue {
  generate: (route: string, parameters?: {[param: string]: string}) => string;
  redirect: (fragment: string, options?: object) => void;
}

export const RouterContext = createContext<RouterContextValue>({
  generate: (route, parameters) => route + (parameters ? '?' + new URLSearchParams(parameters).toString() : ''),
  redirect: () => undefined,
});
