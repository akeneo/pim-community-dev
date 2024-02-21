import React, {createContext, FC, useContext} from 'react';
import {KeyIndicatorsTips} from '../../domain';

export type KeyIndicatorsContextState = {
  tips: KeyIndicatorsTips;
};

export const KeyIndicatorsContext = createContext<KeyIndicatorsContextState>({
  tips: {},
});

export const useKeyIndicatorsContext = (): KeyIndicatorsContextState => {
  return useContext(KeyIndicatorsContext);
};

type ProviderProps = KeyIndicatorsContextState;

export const KeyIndicatorsProvider: FC<ProviderProps> = ({children, ...tips}) => {
  return <KeyIndicatorsContext.Provider value={tips}>{children}</KeyIndicatorsContext.Provider>;
};
