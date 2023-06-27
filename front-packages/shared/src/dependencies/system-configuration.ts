import {SystemConfiguration} from '../DependenciesProvider.type';

const systemConfigurationData = {};

const systemConfiguration: SystemConfiguration = {
  initialize: async () => {
    try {
      const response = await fetch('/system/rest');

      if (!response.ok) {
        throw new Error(response.statusText);
      }

      const data = await response.json();

      systemConfigurationData['sandbox_banner'] = '1' === data.pim_ui___sandbox_banner.value;

      return Promise.resolve();
    } catch (error) {
      return Promise.resolve();
    }
  },

  refresh: async () => await systemConfiguration.initialize(),

  get: (key: string, defaultValue: string | number | boolean | null = null) =>
    systemConfigurationData[key] ?? defaultValue,
};

export {systemConfiguration};
