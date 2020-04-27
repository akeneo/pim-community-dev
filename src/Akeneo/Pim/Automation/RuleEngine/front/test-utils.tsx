import React from 'react';
import { FormContext, useForm } from 'react-hook-form';
import { render } from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import { ThemeProvider } from 'styled-components';
import * as akeneoTheme from './src/theme';
import { ApplicationDependenciesProvider } from './src/dependenciesTools';

jest.mock('./src/dependenciesTools/provider/dependencies.ts');

const LegacyDependencies: React.FC = ({ children }) => (
  <ApplicationDependenciesProvider>{children}</ApplicationDependenciesProvider>
);

const AkeneoThemeProvider: React.FC = ({ children }) => (
  <ThemeProvider theme={akeneoTheme}>{children}</ThemeProvider>
);

const ReactHookFormProvider: React.FC = ({ children }) => {
  const formMethods = useForm();
  return <FormContext {...formMethods}>{children}</FormContext>;
};

const AllProviders: React.FC = ({ children }) => {
  return (
    <ApplicationDependenciesProvider>
      <AkeneoThemeProvider>
        <ReactHookFormProvider>{children}</ReactHookFormProvider>
      </AkeneoThemeProvider>
    </ApplicationDependenciesProvider>
  );
};

type Options = {
  all?: boolean;
  legacy?: boolean;
  theme?: boolean;
  reactHookForm?: boolean;
};

const getProviders = (options: Options) => {
  const { all, legacy, theme, reactHookForm } = options;
  if (theme) {
    return AkeneoThemeProvider;
  }
  if (legacy) {
    return LegacyDependencies;
  }
  if (reactHookForm) {
    return ReactHookFormProvider;
  }
  if (all) {
    return AllProviders;
  }
  throw new Error("[TEST-UTILS]: The provider you asked doesn't exist");
};

export const renderWithProviders = (
  ui: React.ReactElement,
  contextOptions?: Options
) => {
  if (contextOptions) {
    return render(ui, { wrapper: getProviders(contextOptions) });
  }
  return render(ui);
};

export * from '@testing-library/react';
export { renderWithProviders as render };
