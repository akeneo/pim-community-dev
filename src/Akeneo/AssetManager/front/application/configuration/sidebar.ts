import React from 'react';
import {Security} from '@akeneo-pim-community/shared';

class SidebarMissConfigurationError extends Error {}

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

const getTabs = (securityContext: Security, configuration: TabsConfiguration, sidebarIdentifier: string): Tab[] => {
  const viewPathIsNotWellConfigured = undefined === configuration[sidebarIdentifier].tabs;

  if (viewPathIsNotWellConfigured) {
    const confPath = `
config:
  config:
    akeneoassetmanager/application/configuration/sidebar:
      ${sidebarIdentifier}:
        tabs:
          tab-code:
            label: 'your.translation.key.here'`;

    throw new SidebarMissConfigurationError(
      `Cannot get the tabs for "${sidebarIdentifier}". The configuration path should be ${confPath}

Actual conf: ${JSON.stringify(configuration)}`
    );
  }

  return Object.keys(configuration[sidebarIdentifier].tabs)
    .filter((code: string) => {
      const tabConf = configuration[sidebarIdentifier].tabs[code];

      return undefined === tabConf.acl || securityContext.isGranted(tabConf.acl);
    })
    .map((code: string) => {
      const tabConf = configuration[sidebarIdentifier].tabs[code];

      if ('string' !== typeof tabConf.label) {
        const confPath = `
config:
  config:
    akeneoassetmanager/application/configuration/sidebar:
      ${sidebarIdentifier}:
        tabs:
          tab-code:
            label: 'your.translation.key.here'`;
        throw new SidebarMissConfigurationError(`You need to define a label for your tab: ${confPath}`);
      }

      return {code, label: tabConf.label};
    });
};

const getView = (configuration: TabsConfiguration, sidebarIdentifier: string, code: string): typeof React.Component => {
  const viewPathIsNotWellConfigured =
    undefined === configuration[sidebarIdentifier].tabs[code] ||
    undefined === configuration[sidebarIdentifier].tabs[code].view;

  if (viewPathIsNotWellConfigured) {
    const confPath = `
config:
  config:
    akeneoassetmanager/application/configuration/sidebar:
      ${sidebarIdentifier}:
        tabs:
          ${code}:
            view: '@your_view_path_here'`;

    throw new SidebarMissConfigurationError(
      `Cannot load view for tab "${code}". The configuration should look like this ${confPath}

Actual conf: ${JSON.stringify(configuration)}`
    );
  }

  const viewModulePath = configuration[sidebarIdentifier].tabs[code].view;

  return viewModulePath.default;
};

export {getTabs, getView, Tab, TabsConfiguration};
