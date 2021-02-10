import {DependenciesContext, NotificationLevel, RouteParams} from '@akeneo-pim-community/legacy';
import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import Index from './feature';
import {pimTheme} from 'akeneo-design-system';
import translations from './translations.json';
import routes from './routes.json';

const value = {
  notify: (_level: NotificationLevel, message: string) => {
    console.log('notify', message);
  },
  router: {
    generate: (route: string, parameters: RouteParams = {}) => {
      // @ts-ignore
      const routeConf = routes.routes[route];
      if (undefined === routeConf) {
        throw new Error(`Route ${route}, not found`);
      }

      return routeConf.tokens
        .map((token: any) => {
          switch (token[0]) {
            case 'text':
              return token[1];
            case 'variable':
              return token[1] + parameters[token[3]];
            default:
              throw new Error(`Unexpected token type: ${token[0]}`);
          }
        })
        .reverse()
        .join('');
    },
    redirect: (fragment: string, _options?: object) => {
      console.log('redirect', fragment);
    },
  },
  security: {isGranted: (_acl: string) => true},
  translate: (id: string): string => {
    // @ts-ignore
    return translations.messages[`jsmessages:${id}`] ? translations.messages[`jsmessages:${id}`] : id;
  },
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
      <ThemeProvider theme={pimTheme}>
        <Index />
      </ThemeProvider>
    </DependenciesContext.Provider>
  </React.StrictMode>,
  document.getElementById('root')
);
