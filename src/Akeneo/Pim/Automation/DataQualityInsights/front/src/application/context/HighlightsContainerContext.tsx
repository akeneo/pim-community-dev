import React, {createContext, FC, PropsWithChildren, useContext} from 'react';
import {HighlightsContainerState} from '../../infrastructure/hooks/Common/useHighlightsContainerState';

export type HighlightsContainerContextState = HighlightsContainerState & {
  element: Element | null;
};

export const HighlightsContainerContext = createContext<HighlightsContainerContextState>({
  element: null,
  position: {top: 0, left: 0},
  dimension: {width: 0, height: 0},
  scrollPosition: {scrollLeft: 0, scrollTop: 0},
});

HighlightsContainerContext.displayName = 'HighlightsContainerContext';

export const useHighlightsContainerContext = (): HighlightsContainerContextState => {
  return useContext(HighlightsContainerContext);
};

type ProviderProps = HighlightsContainerContextState;
export const HighlightsContainerContextProvider: FC<PropsWithChildren<ProviderProps>> = ({
  children,
  ...initialState
}) => {
  return <HighlightsContainerContext.Provider value={initialState}>{children}</HighlightsContainerContext.Provider>;
};
