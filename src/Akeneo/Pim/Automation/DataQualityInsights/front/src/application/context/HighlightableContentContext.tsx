import React, {createContext, FC, MutableRefObject, useContext} from 'react';
import useHighlightableContentContextState from '../../infrastructure/hooks/Common/useHighlightableContentContextState';

export type HighlightableContentContextState = {
  element: HTMLElement | null;
  mirrorRef: MutableRefObject<HTMLDivElement | null>;
  content: string;
  analyzableContent: string;
  getContentRef: () => HTMLElement | null;
  isActive: boolean;
  activate: () => void;
  deactivate: () => void;
  refresh: () => void;
};

export const HighlightableContentContext = createContext<HighlightableContentContextState>({
  element: null,
  // @ts-ignore
  mirrorRef: {current: null},
  content: '',
  analyzableContent: '',
  getContentRef: () => null,
  isActive: false,
  activate: () => {},
  deactivate: () => {},
  refresh: () => {},
});
HighlightableContentContext.displayName = 'HighlightableContentContext';

export const useHighlightableContentContext = (): HighlightableContentContextState => {
  return useContext(HighlightableContentContext);
};

type HighlightableContentContextProviderProps = {
  element: HTMLElement;
};

export const HighlightableContentContextProvider: FC<HighlightableContentContextProviderProps> = ({
  children,
  element,
}) => {
  const providerState = useHighlightableContentContextState(element);

  return <HighlightableContentContext.Provider value={providerState}>{children}</HighlightableContentContext.Provider>;
};
