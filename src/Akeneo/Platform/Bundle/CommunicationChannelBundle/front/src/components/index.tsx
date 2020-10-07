import * as React from 'react';
// @ts-ignore
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
// @ts-ignore
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
