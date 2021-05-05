import {createContext} from 'react';

type ConfigContextValue = {
  operations_max: number;
  units_max: number;
  families_max: number;
};

const defaultConfigContextValue = {
  operations_max: 5,
  units_max: 50,
  families_max: 100,
};

const ConfigContext = createContext<ConfigContextValue>(defaultConfigContextValue);

export {ConfigContext};
export type {ConfigContextValue};
