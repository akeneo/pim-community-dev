import {useEffect, useState} from 'react';
import {fetchLocaleDictionary} from '../../../fetcher';
import {Word} from '../../../../domain';

type DictionaryState = {
  words: Word[],
  totalWords: number,
};

const useLocaleDictionary = (localeCode: string, page: number, itemsPerPage: number) => {
  const [dictionary, setDictionary] = useState<DictionaryState|null>(null);

  useEffect(() => {
    (async () => {
      const data = await fetchLocaleDictionary(localeCode, page, itemsPerPage);
      setDictionary(data);
    })();
  }, [localeCode]);

  return dictionary;
};

export {DictionaryState, useLocaleDictionary};
