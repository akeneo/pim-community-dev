import {createContext} from 'react';
import {InputValueProps} from '../pages/EditRules/components/actions/attribute';
import {
  CellInputsMapping,
  CellMatchersMapping,
} from '@akeneo-pim-ge/table_attribute';

export type AttributeValueConfig = {
  [attributeType: string]: {
    default: (props: InputValueProps, actionType?: string) => JSX.Element;
  };
};

export type ConfigContextValue = {
  attributeValueConfig: AttributeValueConfig;
  cellInputsMapping: CellInputsMapping;
  cellMatchersMapping: CellMatchersMapping;
};

const defaultConfigContextValue = {
  attributeValueConfig: {},
  cellInputsMapping: {},
  cellMatchersMapping: {},
};

const ConfigContext = createContext<ConfigContextValue>(
  defaultConfigContextValue
);

export {ConfigContext};
