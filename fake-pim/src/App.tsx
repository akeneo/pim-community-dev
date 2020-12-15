import React from 'react';
import './App.css';
import {Test} from '@akeneo-pim-community/raccoon';
import {DependenciesContext} from '@akeneo-pim-community/legacy-bridge';

enum NotificationLevel {
  INFO = 'info',
  SUCCESS = 'success',
  WARNING = 'warning',
  ERROR = 'error',
}

const dependencies = {
  router: {
    generate: (route: string) => route,
    redirect: (url: string) => url,
  },
  translate: (key: string) => {
    switch (key) {
      case 'pim_common.close':
        return 'yeaaaaaah';
      default:
        return 'no';
    }
  },
  viewBuilder: {
    build: (viewName: string) => {
      return Promise.resolve({
        remove: () => {},
        setElement: () => {
          return {
            render: () => viewName,
          };
        },
      });
    },
  },
  notify: (level: NotificationLevel, message: string): string => {
    return `${level} ${message}`;
  },
  user: {
    get: (data: string) => {
      switch (data) {
        case 'catalogLocale':
          return 'en_US';
        case 'uiLocale':
          return 'en_US';
        default:
          return data;
      }
    },
    set: () => {},
  },
  security: {
    isGranted: (_acl: string) => true,
  },
  mediator: {
    trigger: (event: string) => event,
    on: (event: string, _callback: () => void) => event,
  },
};

const Provders = ({children}: {children: React.ReactNode}) => {
  return <DependenciesContext.Provider value={dependencies}>{children}</DependenciesContext.Provider>;
};

function App() {
  return (
    <Provders>
      <Test />
    </Provders>
  );
}

export default App;
