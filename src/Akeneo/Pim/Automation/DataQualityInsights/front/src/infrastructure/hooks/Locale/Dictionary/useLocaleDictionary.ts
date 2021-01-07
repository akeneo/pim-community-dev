import {useEffect, useState} from 'react';
import {fetchLocaleDictionary} from '../../../fetcher';
import {Word} from '../../../../domain';

type DictionaryState = {
  localeCode: string;
  dictionary: Word[] | null;
  totalWords: number;
  itemsPerPage: number;
  currentPage: number;
  setCurrentPage: (page: number) => void;
};

const useLocaleDictionary = (localeCode: string, page: number, itemsPerPage: number): DictionaryState => {
  const [dictionary, setDictionary] = useState<Word[]|null>(null);
  const [totalWords, setTotalWords] = useState<number>(0);
  const [currentPage, setCurrentPage] = useState<number>(page);

  useEffect(() => {
    (async () => {
      const data = await fetchLocaleDictionary(localeCode, currentPage, itemsPerPage);
      setDictionary(data.results);
      setTotalWords(data.total);
    })();
  }, [localeCode, currentPage, itemsPerPage]);

  return {
    localeCode,
    dictionary,
    totalWords,
    itemsPerPage,
    currentPage,
    setCurrentPage,
  };
};

export {DictionaryState, useLocaleDictionary};
