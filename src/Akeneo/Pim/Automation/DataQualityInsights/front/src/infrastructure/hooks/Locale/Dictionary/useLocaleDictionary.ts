import {useCallback, useEffect, useState} from 'react';
import {addWordsToLocaleDictionary, deleteWordFromLocaleDictionary, fetchLocaleDictionary} from '../../../fetcher';
import {Word} from '../../../../domain';

type DictionaryState = {
  localeCode: string;
  dictionary: Word[] | null;
  totalWords: number;
  itemsPerPage: number;
  currentPage: number;
  search: (searchValue: string, pageNumber: number) => void;
  deleteWord: (wordId: number) => void;
  addWords: (words: string[]) => void;
};

const useLocaleDictionary = (localeCode: string, page: number, itemsPerPage: number): DictionaryState => {
  const [dictionary, setDictionary] = useState<Word[] | null>(null);
  const [totalWords, setTotalWords] = useState<number>(0);
  const [currentPage, setCurrentPage] = useState<number>(page);

  const search = useCallback(
    async (searchValue: string, pageNumber: number) => {
      const data = await fetchLocaleDictionary(localeCode, pageNumber, itemsPerPage, searchValue);
      setCurrentPage(pageNumber);
      setDictionary(data.results);
      setTotalWords(data.total);
    },
    [localeCode, currentPage, itemsPerPage]
  );

  const deleteWord = async (wordId: number) => {
    await deleteWordFromLocaleDictionary(wordId);
  };

  const addWords = async (words: string[]) => {
    await addWordsToLocaleDictionary(localeCode, words);
  };

  useEffect(() => {
    search('', 1);
  }, []);

  return {
    localeCode,
    dictionary,
    totalWords,
    itemsPerPage,
    currentPage,
    search,
    deleteWord,
    addWords,
  };
};

export {DictionaryState, useLocaleDictionary};
