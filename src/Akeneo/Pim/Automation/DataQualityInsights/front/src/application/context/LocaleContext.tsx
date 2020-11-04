import React, {createContext, FC, useContext} from 'react';

export type LocaleContextState = {
  locale: string;
};

export const LocaleContext = createContext<LocaleContextState>({
  locale: 'en_US',
});
LocaleContext.displayName = 'LocaleContext';

export const useLocaleContext = () => {
  return useContext(LocaleContext);
};

export const LocaleContextProvider: FC<LocaleContextState> = ({children, ...state}) => {
  return <LocaleContext.Provider value={state}>{children}</LocaleContext.Provider>;
};
