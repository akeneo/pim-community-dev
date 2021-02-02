import React, {createContext, FC} from 'react';
import {LocalesGridDictionariesState, useLocalesGridDictionaries} from '../../hooks';
import {useLocaleSelection} from '../../hooks/locales/useLocaleSelection';
import {LocalesDictionaryInfoCollection} from '../../domain';

const LocalesGridDictionariesContext = createContext<LocalesGridDictionariesState>({
  selectedLocales: [],
  addWordsToDictionaries: () => {},
  refreshDictionaryInfo: () => {},
  localesDictionaryInfo: {},
});

type LocalesGridDictionariesProviderProps = {
  refreshDictionaryInfo: () => void;
  localesDictionaryInfo: LocalesDictionaryInfoCollection;
};

const LocalesGridDictionariesProvider: FC<LocalesGridDictionariesProviderProps> = ({
  refreshDictionaryInfo,
  localesDictionaryInfo,
  children,
}) => {
  const {selectedLocales} = useLocaleSelection();

  const state = useLocalesGridDictionaries(selectedLocales, refreshDictionaryInfo, localesDictionaryInfo);
  return <LocalesGridDictionariesContext.Provider value={state}>{children}</LocalesGridDictionariesContext.Provider>;
};

export {LocalesGridDictionariesProvider, LocalesGridDictionariesContext};
