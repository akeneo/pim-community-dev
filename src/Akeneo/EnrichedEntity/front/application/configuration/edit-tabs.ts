import * as React from 'react';
const requireContext = require('require-context');
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';

class SibebarMissConfigurationError extends Error {}

interface EditTabConfiguration {
  [code: string]: {
    label: string;
    view: string;
  };
}

interface EditTabsConfiguration {
  tabs: EditTabConfiguration;
  default_tab: string;
}

export class EditTabsProvider {
  private configuration: EditTabsConfiguration;
  private requireContext: any;

  private constructor(configuration: EditTabsConfiguration, requireContext: any) {
    this.configuration = configuration;
    this.requireContext = requireContext;
  }

  public static create(configuration: EditTabsConfiguration, requireContext: any): EditTabsProvider {
    return new EditTabsProvider(configuration, requireContext);
  }

  public getTabs(): Tab[] {
    const viewPathIsNotWellConfigured = undefined === this.configuration.tabs;

    if (viewPathIsNotWellConfigured) {
      const confPath = `
config:
    config:
       akeneoenrichedentity/application/configuration/edit-tabs:
            tabs:
                tab-code:
                    view: your_view_path_here
      `;

      throw new SibebarMissConfigurationError(
        `Cannot get the tabs configured. The configuration path should be ${confPath}?`
      );
    }

    return Object.keys(this.configuration.tabs).map((code: string) => {
      return {code, label: this.configuration.tabs[code].label};
    });
  }

  public getDefaultTab(): string {
    const defaultTabIsNotWellConfigured = undefined === this.configuration.default_tab;

    if (defaultTabIsNotWellConfigured) {
      const confPath = `
config:
    config:
       akeneoenrichedentity/application/configuration/edit-tabs:
            default_tab: tab-code
      `;

      throw new SibebarMissConfigurationError(
        `Cannot get the default tab. The configuration path should be ${confPath}?`
      );
    }

    return this.configuration.default_tab;
  }

  public async getView(code: string): Promise<typeof React.Component> {
    const viewPathIsNotWellConfigured =
      undefined === this.configuration.tabs[code] ||
      undefined === this.configuration.tabs[code].view;

    if (viewPathIsNotWellConfigured) {
      const confPath = `
config:
    config:
       akeneoenrichedentity/application/configuration/edit-tabs:
            tabs:
                ${code}:
                    view: your_view_path_here
      `;

      throw new SibebarMissConfigurationError(
        `Cannot load view configuration for tab "${
          code
          }". The configuration path should be ${confPath}?`
      );
    }

    const viewModulePath = this.configuration.tabs[code].view;

    const View: typeof React.Component = await this.loadModule(viewModulePath);

    return View;
  }

  private async loadModule(path: string): Promise<any> {
    const module = await this.requireContext(`${path}`);

    if (typeof module === 'undefined') {
      throw new SibebarMissConfigurationError(
        `The module "${path}" does not exists. You may have an error in your filter configuration file.`
      );
    }

    return module.default;
  }
}

export default EditTabsProvider.create(__moduleConfig as EditTabsConfiguration, requireContext);
