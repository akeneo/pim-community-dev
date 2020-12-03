import React, {createContext, FC} from 'react';
import {useInitialLocalesIndexState} from '../../hooks';
import {Locale} from '../../models';
import {CompareRowDataHandler} from '../shared/providers';

type LocalesIndexState = {
  locales: Locale[];
  isPending: boolean;
  load: () => Promise<void>;
  redirect: (locale: Locale) => void;
  compare: CompareRowDataHandler<Locale>;
};

const LocalesIndexContext = createContext<LocalesIndexState>({
  locales: [],
  isPending: true,
  load: async () => {},
  redirect: () => {},
  compare: () => -1,
});

const LocalesIndexProvider: FC = ({children}) => {
  const state = useInitialLocalesIndexState();
  return <LocalesIndexContext.Provider value={state}>{children}</LocalesIndexContext.Provider>;
};

export {LocalesIndexProvider, LocalesIndexState, LocalesIndexContext};
