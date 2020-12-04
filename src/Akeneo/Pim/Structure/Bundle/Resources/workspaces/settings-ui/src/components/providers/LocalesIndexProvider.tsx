import React, {createContext, FC} from 'react';
import {useInitialLocalesIndexState} from '../../hooks';
import {Locale} from '../../models';

type LocalesIndexState = {
  locales: Locale[];
  isPending: boolean;
  load: () => Promise<void>;
};

const LocalesIndexContext = createContext<LocalesIndexState>({
  locales: [],
  isPending: true,
  load: async () => {},
});

const LocalesIndexProvider: FC = ({children}) => {
  const state = useInitialLocalesIndexState();
  return <LocalesIndexContext.Provider value={state}>{children}</LocalesIndexContext.Provider>;
};

export {LocalesIndexProvider, LocalesIndexState, LocalesIndexContext};
