import React, {createContext, FC} from 'react';
import {ActivatedLocalesState, useActivatedLocales} from '../../hooks';

type LocalesIndexState = ActivatedLocalesState;

const LocalesIndexContext = createContext<LocalesIndexState>({
  locales: [],
  isPending: true,
  load: async () => {},
});

const LocalesIndexProvider: FC = ({children}) => {
  const state = useActivatedLocales();
  return <LocalesIndexContext.Provider value={state}>{children}</LocalesIndexContext.Provider>;
};

export {LocalesIndexProvider, LocalesIndexState, LocalesIndexContext};
