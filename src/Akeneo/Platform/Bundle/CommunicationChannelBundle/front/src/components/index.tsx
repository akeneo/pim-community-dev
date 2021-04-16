import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {Panel} from './panel';

const Index = () => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>
      <Panel />
    </ThemeProvider>
  </DependenciesProvider>
);

export {Index};
