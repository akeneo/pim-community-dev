import React, {createContext, FC, useContext} from 'react';
import useLocales from '../hooks/useLocales';
import {Locale} from '../model';

export const LocalesContext = createContext<Locale[]>([]);
LocalesContext.displayName = 'LocalesContext';

export const useLocalesContext = () => {
  const localesContext = useContext(LocalesContext);
  if (!localesContext) {
    throw new Error('[LocaleContext]: locales context has not been properly initiated');
  }

  return localesContext;
};

export const LocalesContextProvider: FC = ({children}) => {
  const locales = useLocales();

  return <LocalesContext.Provider value={locales}>{children}</LocalesContext.Provider>;
};
