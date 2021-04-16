import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {SystemInfo} from './SystemInfo';

const SystemInfoApp = () => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <SystemInfo />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {SystemInfoApp};
