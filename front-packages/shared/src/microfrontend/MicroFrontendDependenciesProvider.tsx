import React, {ReactNode, useEffect, useState} from 'react';
import {useIsMounted} from '../hooks';
import {RouteParams, View} from '../DependenciesProvider.type';
import {DependenciesContext} from '../DependenciesContext';
import {useNotifications} from './useNotifications';
import {Notifications} from '../components';

type SecurityContext = {
  [acl: string]: boolean;
};

type UserContext = {
  [setting: string]: string;
};

type Translations = {
  locale: string;
  messages: {[key: string]: string | null};
};

type Routes = {
  [key: string]: {tokens: string[][]};
};

type CreateReactAppDependenciesProviderProps = {
  routes: Routes;
  translations: Translations;
  children: ReactNode;
};

const fetcher = async <Type,>(url: string): Promise<Type> => {
  const response = await fetch(url);

  if (401 === response.status) {
    throw new Error('You are not logged in the PIM');
  }

  return await response.json();
};

const MicroFrontendDependenciesProvider = ({
  routes,
  translations,
  children,
}: CreateReactAppDependenciesProviderProps) => {
  const [securityContext, setSecurityContext] = useState<SecurityContext>({});
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

  const view: View = {
    setElement: () => view,
    render: () => {},
    remove: () => {},
  };

  useEffect(() => {
    const fetchUserContext = async () => {
      const json = await fetcher<UserContext>(currentUserUrl);

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
      const json = await fetcher<SecurityContext>(securityContextUrl);

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
        translate: (id: string, placeholders = {}) => {
          const message = translations.messages[`jsmessages:${id}`] ?? id;

          return Object.keys(placeholders).reduce(
            (message, placeholderKey) =>
              message
                // replaceAll is only available in esnext.
                // We don't want to activate it in the tsconfig file as shared package should be as compatible as possible
                // @ts-ignore
                .replaceAll(`{{ ${placeholderKey} }}`, String(placeholders[placeholderKey]))
                // @ts-ignore
                .replaceAll(placeholderKey, String(placeholders[placeholderKey])),
            message
          );
        },
        viewBuilder: {
          build: async (_viewName: string) => Promise.resolve(view),
        },
        mediator: {
          trigger: (event: string, _options?: unknown) => console.log('Triggering', event),
          on: (_event: string, _callback: () => void) => {},
          off: (_event: string, _callback: () => void) => {},
        },
        featureFlags: {
          isEnabled: () => false,
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
