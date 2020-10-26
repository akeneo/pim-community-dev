import React, {createContext, FC, useContext} from 'react';
import {HighlightElement} from '../helper';

export type HighlightsContextState = {
  highlights: HighlightElement[];
};

export const HighlightsContext = createContext<HighlightsContextState>({
  highlights: [],
});
HighlightsContext.displayName = 'HighlightsContext';

export const useHighlightsContext = (): HighlightsContextState => {
  return useContext(HighlightsContext);
};

type ProviderProps = HighlightsContextState;
export const HighlightsContextProvider: FC<ProviderProps> = ({children, ...initialState}) => {
  return <HighlightsContext.Provider value={initialState}>{children}</HighlightsContext.Provider>;
};
