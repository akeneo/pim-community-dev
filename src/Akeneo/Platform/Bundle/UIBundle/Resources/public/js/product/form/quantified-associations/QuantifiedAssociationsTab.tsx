import React from 'react';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {QuantifiedAssociationsProps, QuantifiedAssociations} from './components';
import {pimTheme} from 'akeneo-design-system';

const QuantifiedAssociationsTab = (props: QuantifiedAssociationsProps) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>
      <QuantifiedAssociations {...props} />
    </ThemeProvider>
  </DependenciesProvider>
);

export {QuantifiedAssociationsTab};
