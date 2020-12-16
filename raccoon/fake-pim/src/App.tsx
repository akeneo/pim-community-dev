import React from 'react';
import {Test} from '@akeneo-pim-community/raccoon';
import {TranslateProvider} from '@akeneo-pim-community/legacy';

enum NotificationLevel {
  INFO = 'info',
  SUCCESS = 'success',
  WARNING = 'warning',
  ERROR = 'error',
}

const dependencies = {
  translate: (key: string) => {
    switch (key) {
      case 'pim_common.close':
        return 'yeaaaaaah';
      default:
        return 'no';
    }
  },
};

const Provders = ({children}: {children: React.ReactNode}) => {
  return <TranslateProvider value={dependencies.translate}>{children}</TranslateProvider>;
};

function App() {
  return (
    <Provders>
      <Test />
    </Provders>
  );
}

export default App;
