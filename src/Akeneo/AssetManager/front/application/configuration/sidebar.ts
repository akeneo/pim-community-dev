import * as React from 'react';
import securityContext from 'akeneoassetmanager/tools/security-context';

class SibebarMissConfigurationError extends Error {}

type Tab = {code: string; label: string};

interface TabConfiguration {
  [code: string]: {
    label: string | {label: typeof React.Component};
    view: {default: typeof React.Component};
    acl?: string;
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
        akeneoassetmanager/application/configuration/sidebar:
            ${sidebarIdentifier}:
                tabs:
                    tab-code:
                        label: 'your.translation.key.here'`;

      throw new SibebarMissConfigurationError(
        `Cannot get the tabs for "${sidebarIdentifier}". The configuration path should be ${confPath}

Actual conf: ${JSON.stringify(this.configuration)}`
      );
    }

    return Object.keys(this.configuration[sidebarIdentifier].tabs)
      .filter((code: string) => {
        const tabConf = this.configuration[sidebarIdentifier].tabs[code];

        return undefined === tabConf.acl || securityContext.isGranted(tabConf.acl);
      })
      .map((code: string) => {
        const tabConf = this.configuration[sidebarIdentifier].tabs[code];

        if ('string' !== typeof tabConf.label) {
          const confPath = `
config:
    config:
        akeneoassetmanager/application/configuration/sidebar:
            ${sidebarIdentifier}:
                tabs:
                    tab-code:
                        label: 'your.translation.key.here'`;
          throw new SibebarMissConfigurationError(`You need to define a label for your tab: ${confPath}`);
        }

        return {code, label: tabConf.label};
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
        akeneoassetmanager/application/configuration/sidebar:
            ${sidebarIdentifier}:
                tabs:
                    ${code}:
                        view: '@your_view_path_here'`;

      throw new SibebarMissConfigurationError(
        `Cannot load view for tab "${code}". The configuration should look like this ${confPath}

Actual conf: ${JSON.stringify(this.configuration)}`
      );
    }

    const viewModulePath = this.configuration[sidebarIdentifier].tabs[code].view;

    return viewModulePath.default;
  }
}

export {Tab};
export default TabsProvider.create(__moduleConfig as TabsConfiguration);
