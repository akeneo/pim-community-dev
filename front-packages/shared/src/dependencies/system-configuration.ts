import {SystemConfiguration} from '../DependenciesProvider.type';

const systemConfigurationData = {};

const systemConfiguration: SystemConfiguration = {
  initialize: async () => {
    const response = await fetch('/system/rest');
    const data = await response.json();

    systemConfigurationData['sandbox_banner'] = '1' === data.pim_ui___sandbox_banner.value;

    return Promise.resolve();
  },

  refresh: async () => await systemConfiguration.initialize(),

  get: (key: string, defaultValue: string | number | boolean | null = null) =>
    systemConfigurationData[key] ?? defaultValue,
};

export {systemConfiguration};
