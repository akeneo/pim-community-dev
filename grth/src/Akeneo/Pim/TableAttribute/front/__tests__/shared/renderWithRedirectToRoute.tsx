import React from 'react';
import {render} from '@testing-library/react';
import {DependenciesContext, RouteParams} from '@akeneo-pim-community/shared';
import {dependencies} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

export const renderWithRedirectToRoute = (
  ui: React.ReactElement,
  redirectToRoute: (route: string, parameters?: RouteParams) => void
) => {
  const customDependencies = dependencies;
  if (typeof customDependencies === 'undefined') {
    throw new Error('Dependencies are not defined');
  }
  if (typeof customDependencies.router === 'undefined') {
    throw new Error('Router is not defined');
  }
  customDependencies.router.redirectToRoute = redirectToRoute;

  const Wrapper: React.FC = ({children}) => {
    return (
      <DependenciesContext.Provider value={customDependencies}>
        <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
      </DependenciesContext.Provider>
    );
  };

  return render(ui, {wrapper: Wrapper});
};
