import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AssociationTypesIndex} from '@akeneo-pim-community/settings-ui';

const AssociationTypesApp = () => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AssociationTypesIndex />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {AssociationTypesApp};
