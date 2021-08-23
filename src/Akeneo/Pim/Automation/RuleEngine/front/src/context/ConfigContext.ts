import {createContext} from 'react';
import {InputValueProps} from '../pages/EditRules/components/actions/attribute';

export type AttributeValueConfig = {
  [attributeType: string]: {
    default: (props: InputValueProps, actionType?: string) => JSX.Element;
  };
};

export type ConfigContextValue = {
  attributeValueConfig: AttributeValueConfig;
};

const defaultConfigContextValue = {
  attributeValueConfig: {},
};

const ConfigContext = createContext<ConfigContextValue>(
  defaultConfigContextValue
);

export {ConfigContext};
