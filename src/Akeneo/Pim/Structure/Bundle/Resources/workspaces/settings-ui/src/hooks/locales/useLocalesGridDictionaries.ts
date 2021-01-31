import {useCallback} from 'react';
import {addWordsToLocalesDictionaries} from '../../infrastructure';
import {LocalesDictionaryInfoCollection} from '../../domain';

type LocalesGridDictionariesState = {
  selectedLocales: string[];
  addWordsToDictionaries: (words: string[]) => void;
  refreshDictionaryInfo: () => void;
  localesDictionaryInfo: LocalesDictionaryInfoCollection;
};

const useLocalesGridDictionaries = (
  selectedLocales: string[],
  refreshDictionaryInfo: () => void,
  localesDictionaryInfo: LocalesDictionaryInfoCollection
): LocalesGridDictionariesState => {
  const addWordsToDictionaries = useCallback(
    async (words: string[]) => {
      await addWordsToLocalesDictionaries(selectedLocales, words);
    },
    [selectedLocales]
  );

  return {
    selectedLocales,
    addWordsToDictionaries,
    refreshDictionaryInfo,
    localesDictionaryInfo,
  };
};

export {useLocalesGridDictionaries, LocalesGridDictionariesState};
