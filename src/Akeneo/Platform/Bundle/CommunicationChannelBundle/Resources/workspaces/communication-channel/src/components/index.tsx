import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {Panel} from './panel';

const Index = () => (
  <DependenciesProvider>
    <AkeneoThemeProvider>
      <Panel />
    </AkeneoThemeProvider>
  </DependenciesProvider>
);

export {Index};
