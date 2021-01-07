import React, {createContext, FC} from 'react';
import {DictionaryState, useLocaleDictionary} from '../../../infrastructure';

const LocalesIndexContext = createContext<DictionaryState>({
  words: [],
  totalWords: 0,
});

type LocalesIndexProviderProps = {
  localeCode: string;
}

const LocalesIndexProvider: FC<LocalesIndexProviderProps> = ({localeCode, children}) => {
  const state = useLocaleDictionary();
  return <LocalesIndexContext.Provider value={state}>{children}</LocalesIndexContext.Provider>;
};

export {LocalesIndexProvider, DictionaryState, LocalesIndexContext};
