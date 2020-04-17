import { render } from "@testing-library/react";
import "@testing-library/jest-dom/extend-expect";
import React from "react";
import { ThemeProvider } from "styled-components";
import * as akeneoTheme from "./src/theme";
import { ApplicationDependenciesProvider } from "./src/dependenciesTools";

jest.mock("./src/dependenciesTools/provider/dependencies.ts");

const LegacyDependencies: React.FC = ({ children }) => (
  <ApplicationDependenciesProvider>{children}</ApplicationDependenciesProvider>
);

const AkeneoThemeProvider: React.FC = ({ children }) => (
  <ThemeProvider theme={akeneoTheme}>{children}</ThemeProvider>
);

const AllProviders: React.FC = ({ children }) => {
  return (
    <ApplicationDependenciesProvider>
      <AkeneoThemeProvider>{children}</AkeneoThemeProvider>
    </ApplicationDependenciesProvider>
  );
};

type Options = {
  all?: boolean;
  legacy?: boolean;
  theme?: boolean;
};

const getProviders = (options: Options) => {
  const { all, legacy, theme } = options;
  if (theme) {
    return AkeneoThemeProvider;
  }
  if (legacy) {
    return LegacyDependencies;
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

export * from "@testing-library/react";
export { renderWithProviders as render };
