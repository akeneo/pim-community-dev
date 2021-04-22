import React, {ReactNode, useEffect, useState} from 'react';
import {DependenciesContext, useIsMounted} from '@akeneo-pim-community/shared';
import {dependencies} from './dependencies';

type CreateReactAppDependenciesProviderProps = {
  children: ReactNode;
}

const MicroFrontendDependenciesProvider = ({children}: CreateReactAppDependenciesProviderProps) => {
  const [securityContext, setSecurityContext] = useState({});
  const [userContext, setUserContext] = useState({});
  const isMounted = useIsMounted();

  useEffect(() => {
    fetch('/rest/user')
      .then(response => response.json())
      .then(jsonResponse => {
        if (isMounted()) {
          setSecurityContext({
            ...{jsonResponse},
            uiLocale: jsonResponse.user_default_locale,
            catalogLocale: jsonResponse.catalog_default_locale,
            catalogScope: jsonResponse.catalog_default_scope,
          });
        }
      });

    fetch('/rest/security')
      .then(response => response.json())
      .then(jsonResponse => {
        if (isMounted()) {
          setUserContext(jsonResponse);
        }
      });
  }, []);

  return (
    <DependenciesContext.Provider value={dependencies}>
      {children}
    </DependenciesContext.Provider>
  )
}

export {MicroFrontendDependenciesProvider};
