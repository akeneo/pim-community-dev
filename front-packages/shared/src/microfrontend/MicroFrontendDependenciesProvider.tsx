import React, {ReactNode, useEffect, useState} from 'react';
import {useIsMounted} from '../hooks';
import {RouteParams} from '../DependenciesProvider.type';
import {DependenciesContext} from '../DependenciesContext';
import {useNotifications} from './useNotifications';
import {Notifications} from '../components';

type Translations = {
  locale: string;
  messages: {[key: string]: string};
};

type Routes = {
  [key: string]: {tokens: string[][]};
};

type CreateReactAppDependenciesProviderProps = {
  routes: Routes;
  translations: Translations;
  children: ReactNode;
};

const MicroFrontendDependenciesProvider = ({
  routes,
  translations,
  children,
}: CreateReactAppDependenciesProviderProps) => {
  const [securityContext, setSecurityContext] = useState({});
  const [userContext, setUserContext] = useState({});
  const [notifications, notify, handleNotificationClose] = useNotifications();
  const isMounted = useIsMounted();

  const generateUrl = (route: string, parameters?: RouteParams) => {
    const routeConf = routes[route];

    if (undefined === routeConf) {
      throw new Error(`Route ${route} not found`);
    }

    const queryString = parameters
      ? '?' +
        Object.entries(parameters)
          .map(([key, val]) => `${key}=${val}`)
          .join('&')
      : '';

    return (
      routeConf.tokens
        .map((token: string[]) => {
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
        .join('') + queryString
    );
  };

  const currentUserUrl = generateUrl('pim_user_user_rest_get_current');
  const securityContextUrl = generateUrl('pim_user_security_rest_get');

  useEffect(() => {
    const fetchUserContext = async () => {
      const response = await fetch(currentUserUrl);
      const json = await response.json();

      if (isMounted()) {
        setUserContext({
          ...json,
          uiLocale: json.user_default_locale,
          catalogLocale: json.catalog_default_locale,
          catalogScope: json.catalog_default_scope,
        });
      }
    };

    const fetchSecurityContext = async () => {
      const response = await fetch(securityContextUrl);
      const json = await response.json();

      if (isMounted()) {
        setSecurityContext(json);
      }
    };

    fetchUserContext();
    fetchSecurityContext();
  }, [currentUserUrl, securityContextUrl, isMounted]);

  return (
    <DependenciesContext.Provider
      value={{
        notify,
        user: {
          get: (data: string) => userContext[data],
          set: (key: string, value: string) => setUserContext(userContext => ({...userContext, [key]: value})),
        },
        security: {isGranted: (acl: string) => securityContext[acl] === true},
        router: {
          generate: generateUrl,
          redirect: (_fragment: string, _options?: object) => alert('Not implemented'),
        },
        translate: (id: string) => translations.messages[`jsmessages:${id}`] ?? id,
        viewBuilder: {
          build: async (_viewName: string) => Promise.resolve(),
        },
        mediator: {
          trigger: (event: string, _options?: unknown) => console.log('Triggering', event),
          on: (_event: string, _callback: () => void) => {},
          off: (_event: string, _callback: () => void) => {},
        },
      }}
    >
      <Notifications notifications={notifications} onNotificationClosed={handleNotificationClose} />
      {children}
    </DependenciesContext.Provider>
  );
};

export {MicroFrontendDependenciesProvider};
export type {Routes, Translations};
