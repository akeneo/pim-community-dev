import * as React from 'react';
const requireContext = require('require-context');
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';

class SibebarMissConfigurationError extends Error {}

interface TabConfiguration {
  [code: string]: {
    label: string;
    view: string;
  };
}

interface TabsConfiguration {
  [sidebarIdentifier: string]: {
    tabs: TabConfiguration;
  };
}

export class TabsProvider {
  private configuration: TabsConfiguration;
  private requireContext: any;

  private constructor(configuration: TabsConfiguration, requireContext: any) {
    this.configuration = configuration;
    this.requireContext = requireContext;
  }

  public static create(configuration: TabsConfiguration, requireContext: any): TabsProvider {
    return new TabsProvider(configuration, requireContext);
  }

  public getTabs(sidebarIdentifier: string): Tab[] {
    const viewPathIsNotWellConfigured = undefined === this.configuration[sidebarIdentifier].tabs;

    if (viewPathIsNotWellConfigured) {
      const confPath = `
config:
    config:
        akeneoenrichedentity/application/configuration/sidebar:
            ${sidebarIdentifier}:
                tabs:
                    tab-code:
                        view: your_view_path_here
      `;

      throw new SibebarMissConfigurationError(
        `Cannot get the tabs configured. The configuration path should be ${confPath}?`
      );
    }

    return Object.keys(this.configuration[sidebarIdentifier].tabs).map((code: string) => {
      return {code, label: this.configuration[sidebarIdentifier].tabs[code].label};
    });
  }

  public async getView(sidebarIdentifier: string, code: string): Promise<typeof React.Component> {
    const viewPathIsNotWellConfigured =
      undefined === this.configuration[sidebarIdentifier].tabs[code] ||
      undefined === this.configuration[sidebarIdentifier].tabs[code].view;

    if (viewPathIsNotWellConfigured) {
      const confPath = `
config:
    config:
        akeneoenrichedentity/application/configuration/sidebar:
            ${sidebarIdentifier}:
                tabs:
                    ${code}:
                        view: your_view_path_here
      `;

      throw new SibebarMissConfigurationError(
        `Cannot load view configuration for tab "${code}". The configuration path should be ${confPath}?`
      );
    }

    const viewModulePath = this.configuration[sidebarIdentifier].tabs[code].view;

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

export default TabsProvider.create(__moduleConfig as TabsConfiguration, requireContext);
