import React, {ReactNode, useContext} from 'react';
import {ValueConfig} from '../configuration/value';
import {TabsConfiguration} from '../configuration/sidebar';
import {AttributeConfig} from '../configuration/attribute';

type Config = {value: ValueConfig; sidebar: TabsConfiguration; attribute: AttributeConfig};

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
