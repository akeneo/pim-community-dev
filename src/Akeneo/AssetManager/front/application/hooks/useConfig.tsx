import React, {ReactNode, useContext} from 'react';
import {ValueConfig} from '../configuration/value';

type Config = {value: ValueConfig};

const ConfigContext = React.createContext<Config | null>(null);

type ConfigProviderProps = {
  config: Config;
  children: ReactNode;
};

const ConfigProvider = ({config, children}: ConfigProviderProps) => {
  return <ConfigContext.Provider value={config}>{children}</ConfigContext.Provider>;
};

const useConfig = () => useContext(ConfigContext);

export {useConfig, ConfigProvider};
