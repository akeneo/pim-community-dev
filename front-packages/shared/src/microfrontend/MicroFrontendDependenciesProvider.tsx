import React, {ReactNode, useCallback, useEffect, useMemo, useState} from 'react';
import {useIsMounted} from '../hooks';
import {RouteParams, View} from '../DependenciesProvider.type';
import {DependenciesContext} from '../DependenciesContext';
import {useNotifications} from './useNotifications';
import {Notifications} from '../components';
import {createQueryParam} from './model/queryParam';
import {initTranslator, translate, userContext as LegacyUserContext} from '../dependencies';
import {UserContext} from '../DependenciesProvider.type';

type SecurityContext = {
  [acl: string]: boolean;
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
  translations?: Translations;
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
  const [userContext, setUserContext] = useState<UserContext>({get: key => key, set: () => {}});
  const [notifications, notify, handleNotificationClose] = useNotifications();
  const isMounted = useIsMounted();
  const [translator, setTranslator] = useState(() => {
    if (translations !== undefined) {
      console.warn('The "translations" option MicroFrontendDependenciesProvider is deprecated.');

      return (id: string, placeholders = {}) => {
        const message = translations?.messages[`jsmessages:${id}`] ?? id;

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
      };
    }

    return () => '';
  });

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

  const securityContextUrl = generateUrl('pim_user_security_rest_get');

  const view: View = {
    setElement: () => view,
    render: () => {},
    remove: () => {},
    setData: () => {},
  };

  useEffect(() => {
    const fetchSecurityContext = async () => {
      const json = await fetcher<SecurityContext>(securityContextUrl);

      if (isMounted()) {
        setSecurityContext(json);
      }
    };

    fetchSecurityContext();

    // @ts-ignore
    LegacyUserContext.initialize().then(() => {
      setUserContext(LegacyUserContext);

      if (translations !== undefined) {
        return;
      }

      initTranslator.fetch().then(() => setTranslator(() => translate));
    });
  }, [securityContextUrl, isMounted]);

  const dependencies = useMemo(
    () => ({
      notify,
      user: userContext,
      security: {isGranted: (acl: string) => securityContext[acl] === true},
      router: {
        generate: generateUrl,
        redirect: (_fragment: string, _options?: object) => console.info('Not implemented'),
        redirectToRoute: (_route: string, _parameters?: object) => console.info('Not implemented'),
      },
      translate: translator,
      viewBuilder: {
        build: async (_viewName: string) => view,
      },
      mediator: {
        trigger: (event: string, _options?: unknown) => console.log('Triggering', event),
        on: (_event: string, _callback: () => void) => {},
        off: (_event: string, _callback: () => void) => {},
      },
      featureFlags: {
        isEnabled: () => false,
      },
      analytics: {
        track: (event: string, properties?: object) => console.log('Track event', event, properties),
        appcuesTrack: (event: string, properties?: object) => console.log('Track event', event, properties),
      },
    }),
    [notify, userContext, securityContext, translations, generateUrl, translator]
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
