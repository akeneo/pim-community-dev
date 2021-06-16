import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {GroupTypesIndex} from '@akeneo-pim-community/settings-ui';

const GroupTypesApp = () => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <GroupTypesIndex />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {GroupTypesApp};
