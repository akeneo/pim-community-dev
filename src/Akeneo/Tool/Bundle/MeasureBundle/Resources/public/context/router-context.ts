import {createContext} from 'react';

type RouterContextValue = {
  generate: (route: string, parameters?: {[param: string]: string}) => string;
  redirect: (fragment: string, options?: object) => void;
};

const RouterContext = createContext<RouterContextValue>({
  generate: (route, parameters) => route + (parameters ? '?' + new URLSearchParams(parameters).toString() : ''),
  redirect: () => undefined,
});

export {RouterContextValue, RouterContext};
