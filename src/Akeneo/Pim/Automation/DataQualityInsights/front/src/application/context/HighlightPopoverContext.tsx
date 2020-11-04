import React, {createContext, FC, useContext} from 'react';
import {HighlightElement} from '../helper';

export type HighlightPopoverContextState = {
  activeElement: HTMLElement | null;
  activeHighlight: HighlightElement | null;
  hide: () => void;
};

export const HighlightPopoverContext = createContext<HighlightPopoverContextState>({
  activeElement: null,
  activeHighlight: null,
  hide: () => {},
});

HighlightPopoverContext.displayName = 'HighlightPopoverContext';

export const useHighlightPopoverContext = (): HighlightPopoverContextState => {
  return useContext(HighlightPopoverContext);
};

type ProviderProps = HighlightPopoverContextState;

export const HighlightPopoverContextProvider: FC<ProviderProps> = ({children, ...initialState}) => {
  return <HighlightPopoverContext.Provider value={initialState}>{children}</HighlightPopoverContext.Provider>;
};
