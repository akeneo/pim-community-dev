import React from 'react';
import {render} from '@testing-library/react';
import {DependenciesContext} from '@akeneo-pim-community/shared';
import {dependencies} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

export const renderWithFeatureFlag = (ui: React.ReactElement, customFeatureFlags = {}) => {
  const customDependencies = dependencies;
  if (typeof customDependencies === 'undefined') {
    throw new Error('Dependencies are not defined');
  }
  customDependencies.featureFlags = {
    isEnabled: featureFlag => {
      return customFeatureFlags[featureFlag] || false;
    },
  };

  const Wrapper: React.FC = ({children}) => {
    return (
      <DependenciesContext.Provider value={customDependencies}>
        <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
      </DependenciesContext.Provider>
    );
  };

  return render(ui, {wrapper: Wrapper});
};
