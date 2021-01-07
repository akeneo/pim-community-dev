import React, {createContext, FC} from 'react';
import {DictionaryState, useLocaleDictionary} from '../../../infrastructure';

const DictionaryContext = createContext<DictionaryState>({
  localeCode: '',
  dictionary: [],
  totalWords: 0,
  itemsPerPage: 1,
  currentPage: 1,
  setCurrentPage: (page: number) => page,
});

type DictionaryProviderProps = {
  localeCode: string;
}

const DictionaryProvider: FC<DictionaryProviderProps> = ({localeCode, children}) => {
  const state = useLocaleDictionary(localeCode, 1, 25);
  return <DictionaryContext.Provider value={state}>{children}</DictionaryContext.Provider>;
};

export {DictionaryProvider, DictionaryContext};
