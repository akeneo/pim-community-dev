import * as React from 'react';
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';

class SibebarMissConfigurationError extends Error {}

interface TabConfiguration {
  [code: string]: {
    label: string;
    view: {default: typeof React.Component};
  };
}

interface TabsConfiguration {
  [sidebarIdentifier: string]: {
    tabs: TabConfiguration;
  };
}

export class TabsProvider {
  private configuration: TabsConfiguration;

  private constructor(configuration: TabsConfiguration) {
    this.configuration = configuration;
  }

  public static create(configuration: TabsConfiguration): TabsProvider {
    return new TabsProvider(configuration);
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
                        view: '@your_view_path_here'
      `;

      throw new SibebarMissConfigurationError(
        `Cannot get the tabs configured. The configuration path should be ${confPath}?
Actual conf: ${JSON.stringify(this.configuration)}`
      );
    }

    return Object.keys(this.configuration[sidebarIdentifier].tabs).map((code: string) => {
      return {code, label: this.configuration[sidebarIdentifier].tabs[code].label};
    });
  }

  public getView(sidebarIdentifier: string, code: string): typeof React.Component {
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
                        view: '@your_view_path_here'
      `;

      throw new SibebarMissConfigurationError(
        `Cannot load view configuration for tab "${code}". The configuration path should be ${confPath}?
Actual conf: ${JSON.stringify(this.configuration)}`
      );
    }

    const viewModulePath = this.configuration[sidebarIdentifier].tabs[code].view;

    return viewModulePath.default;
  }
}

export default TabsProvider.create(__moduleConfig as TabsConfiguration);
