import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {QuantifiedAssociationsProps, QuantifiedAssociations} from './components';

const QuantifiedAssociationsTab = (props: QuantifiedAssociationsProps) => (
  <DependenciesProvider>
    <AkeneoThemeProvider>
      <QuantifiedAssociations {...props} />
    </AkeneoThemeProvider>
  </DependenciesProvider>
);

export {QuantifiedAssociationsTab};
