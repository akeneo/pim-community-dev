import {DependenciesContext, NotificationLevel, RouteParams} from '@akeneo-pim-community/legacy';
import React from 'react';
import ReactDOM from 'react-dom';
import {Index} from './feature';

const value = {
  notify: (_level: NotificationLevel, message: string) => {
    console.log('notify', message);
  },
  router: {
    generate: (route: string, _parameters?: RouteParams) => route,
    redirect: (fragment: string, _options?: object) => {
      console.log('redirect', fragment);
    },
  },
  security: {isGranted: (_acl: string) => true},
  translate: (id: string) => `translation_from_cra.${id}`,
  user: {
    get: (data: string) => {
      console.log('getData', data);
      return data;
    },
    set: (key: string, value: string, _options: {}) => {
      console.log('set', key, value);
    },
  },
  viewBuilder: {
    build: async (_viewName: string) => Promise.resolve(),
  },
  mediator: {
    trigger: (event: string, _options?: unknown) => {
      console.log(event);
    },
    on: (_event: string, _callback: () => void) => {},
  },
};

ReactDOM.render(
  <React.StrictMode>
    <DependenciesContext.Provider value={value}>
      <Index />
    </DependenciesContext.Provider>
  </React.StrictMode>,
  document.getElementById('root')
);
