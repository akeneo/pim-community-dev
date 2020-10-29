import React from 'react';
import {FormContext, useForm} from 'react-hook-form';
import {render} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {ThemeProvider} from 'styled-components';
import * as akeneoTheme from './src/theme';
import {ApplicationDependenciesProvider} from './src/dependenciesTools';

jest.mock('./src/dependenciesTools/provider/dependencies.ts');

type ToRegister = {
  name: string;
};

type ReactHookFormWrapperInitializer = {
  defaultValues?: any;
  toRegister?: ToRegister[];
};

const LegacyDependencies: React.FC = ({children}) => (
  <ApplicationDependenciesProvider>{children}</ApplicationDependenciesProvider>
);

const AkeneoThemeProvider: React.FC = ({children}) => <ThemeProvider theme={akeneoTheme}>{children}</ThemeProvider>;

type Context = {
  all?: boolean;
  legacy?: boolean;
  theme?: boolean;
  reactHookForm?: boolean;
};

const getProviders = (context: Context) => {
  const {legacy, theme} = context;
  if (theme) {
    return AkeneoThemeProvider;
  }
  if (legacy) {
    return LegacyDependencies;
  }
  throw new Error("[TEST-UTILS]: The provider you asked doesn't exist");
};

export const renderWithProviders = (ui: React.ReactElement, context?: Context, options?: any) => {
  /*
    Providers with react-hook-form needs to be define here, to give them some specific props.
  */
  const ReactHookFormProvider: React.FC<ReactHookFormWrapperInitializer> = ({
    children,
    defaultValues = {},
    toRegister = [],
  }) => {
    const form = useForm({defaultValues});
    const {register} = form;
    React.useEffect(() => {
      toRegister?.forEach(register);
    }, [register]);
    return <FormContext {...form}>{children}</FormContext>;
  };
  const AllProviders: React.FC = ({children}) => {
    return (
      <LegacyDependencies>
        <AkeneoThemeProvider>
          <ReactHookFormProvider defaultValues={options?.defaultValues} toRegister={options?.toRegister}>
            {children}
          </ReactHookFormProvider>
        </AkeneoThemeProvider>
      </LegacyDependencies>
    );
  };
  if (context?.all) {
    return render(ui, {wrapper: AllProviders});
  } else if (context?.reactHookForm) {
    return render(ui, {wrapper: ReactHookFormProvider});
  } else if (context) {
    return render(ui, {wrapper: getProviders(context)});
  }
  return render(ui);
};

export * from '@testing-library/react';
export {renderWithProviders as render};
