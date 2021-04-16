import {DashboardContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/DashboardContext';
import React, {FC, ReactElement} from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {render} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/shared';

const renderDashboardWithProvider = (ui: ReactElement) => {
  const Wrapper: FC = ({children}) => (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <DashboardContextProvider>{children}</DashboardContextProvider>
      </ThemeProvider>
    </DependenciesProvider>
  );

  return render(ui, {
    wrapper: Wrapper,
  });
};

export {renderDashboardWithProvider};
