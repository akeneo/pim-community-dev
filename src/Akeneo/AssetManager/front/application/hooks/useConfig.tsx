import React, {ReactNode, useContext} from 'react';
import {getValueConfig, ValueConfig} from '../configuration/value';

const config = {
  value: getValueConfig(),
};

type Config = {value: ValueConfig};

const ConfigContext = React.createContext<Config>(config);

type ConfigProviderProps = {
  config: Config;
  children: ReactNode;
};

const ConfigProvider = ({config, children}: ConfigProviderProps) => {
  return <ConfigContext.Provider value={config}>{children}</ConfigContext.Provider>;
};

const useConfig = (key: keyof Config) => {
  const config = useContext(ConfigContext);

  if (!(undefined !== config && key in config && undefined !== config[key])) {
    throw new Error('Invalid config key');
  }

  return config[key];
};

export {useConfig, ConfigProvider};
