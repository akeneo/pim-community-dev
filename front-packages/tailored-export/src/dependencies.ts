import {NotificationLevel, RouteParams} from '@akeneo-pim-community/shared';
import translations from './translations.json';
import routes from './routes.json';

const generate = (route: string, parameters?: RouteParams) => {
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
        if (parameters === undefined) {
          throw new Error(`Missing parameter: ${token[3]}`);
        }
      return token[1] + parameters[token[3]];
        default:
        throw new Error(`Unexpected token type: ${token[0]}`);
    }
  })
  .reverse()
  .join('');
};

let securityAcl = {};
const isGranted = (_acl: string) => {
  if(securityAcl) {
    return false;
  }
  return false;
}

const dependencies = {
  notify: (_level: NotificationLevel, message: string) => {
    console.log('notify', message);
  },
  router: {
    generate,
    redirect: (_fragment: string, _options?: object) => {
      alert('Not implemented');
    },
  },
  security: {isGranted},
  translate: (id: string) => {
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

export {dependencies};
