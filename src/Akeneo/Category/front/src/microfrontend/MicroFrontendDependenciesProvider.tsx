import React, {ReactNode, useCallback, useEffect, useMemo, useState} from 'react';
import {DependenciesContext, Notifications, RouteParams, useIsMounted, View} from '@akeneo-pim-community/shared';
import {createQueryParam} from './model/queryParam';
import {useNotifications} from './useNotifications';
import {useConfiguration} from '../configuration';

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
  const {configuration} = useConfiguration();

  const isMounted = useIsMounted();

  const generateUrl = useCallback(
    (route: string, parameters?: RouteParams) => {
      const routeConf = routes[route];

      if (undefined === routeConf) {
        throw new Error(`Route ${route} not found`);
      }

      const queryString = createQueryParam(parameters);

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
    },
    [routes]
  );

  const currentUserUrl = generateUrl('pim_user_user_rest_get_current');
  const securityContextUrl = generateUrl('pim_user_security_rest_get');

  const view: View = {
    setElement: () => view,
    render: () => {},
    remove: () => {},
    setData: () => {},
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
        setSecurityContext({
          ...json,
          ...configuration.acls,
        });
      }
    };

    fetchUserContext();
    fetchSecurityContext();
  }, [currentUserUrl, securityContextUrl, isMounted, configuration]);

  const dependencies = useMemo(
    () => ({
      notify,
      user: {
        get: (data: string) => userContext[data],
        set: (key: string, value: string) => setUserContext(userContext => ({...userContext, [key]: value})),
      },
      security: {isGranted: (acl: string) => securityContext[acl] === true},
      router: {
        generate: generateUrl,
        redirect: (_fragment: string, _options?: object) => console.info('Not implemented'),
        redirectToRoute: (_route: string, _parameters?: object) => console.info('Not implemented'),
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
        isEnabled: (feature: string) => {
          // @ts-ignore
          return configuration.features[feature] ?? false;
        },
      },
      analytics: {
        track: (event: string, properties?: object) => console.log('Track event', event, properties),
      },
    }),
    [notify, userContext, securityContext, translations, generateUrl, configuration]
  );

  return (
    <DependenciesContext.Provider value={dependencies}>
      <Notifications notifications={notifications} onNotificationClosed={handleNotificationClose} />
      {children}
    </DependenciesContext.Provider>
  );
};

export {MicroFrontendDependenciesProvider};
export type {Routes, Translations};
